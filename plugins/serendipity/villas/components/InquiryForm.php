<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Serendipity\Villas\Classes\LayoutDownloadAccess;
use Serendipity\Villas\Models\Inquiry;
use Serendipity\Villas\Models\Villa;

class InquiryForm extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Inquiry Form',
            'description' => 'AJAX inquiry form for villa viewings.'
        ];
    }

    public function onSend()
    {
        $data = post();

        // Honeypot: simple bot trap
        if (!empty($data['website'])) {
            return; // silently ignore
        }

        $rateKey = 'villa-inquiry:'.hash('sha256', request()->ip() ?? '');
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw new \ApplicationException('Please wait a minute before sending another request.');
        }
        RateLimiter::hit($rateKey, 60);

        // Resolve context from the page route, never from a submitted villa ID/title.
        $villa = Villa::with('layouts')->where('slug', $this->getController()->param('slug'))->first();
        if (!$villa) {
            throw new \ApplicationException('This villa could not be found. Please refresh the page.');
        }
        $wantsLayouts = ($data['request_type'] ?? 'inquiry') === 'layouts';
        $data['villa_id'] = $villa->id;
        $data['villa_title'] = $villa->title;
        $data['source_url'] = url('/villas/'.rawurlencode($villa->slug));

        $rules = [
            'request_type' => 'sometimes|in:inquiry,layouts',
            'name' => $wantsLayouts ? 'nullable|string|max:255' : 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255',
            'message' => $wantsLayouts ? 'nullable|string|max:5000' : 'required|string|min:10|max:5000',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new \ValidationException($validator);
        }

        if ($wantsLayouts && (!$villa->enable_layouts_download || !$villa->layouts->count())) {
            throw new \ApplicationException('Layouts are not currently available for this villa. You can still send us a question.');
        }

        $data['name'] = trim($data['name'] ?? '') ?: 'Not provided';
        $data['email'] = trim($data['email']);
        $data['message'] = trim($data['message'] ?? '');
        if ($wantsLayouts) {
            $data['message'] = '[Villa layouts requested]'.($data['message'] ? "\n\n".$data['message'] : '');
        }

        // Persist to database
        $record = Inquiry::create([
            'villa_id' => $data['villa_id'],
            'villa_title' => $data['villa_title'],
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'source_url' => $data['source_url'],
        ]);
        Log::info('Inquiry saved', ['id' => $record->id]);

        // Build safe payload for mail view (avoid reserved `message` variable)
        $mailData = [
            'villa_title' => $data['villa_title'],
            'name'        => $data['name'],
            'email'       => $data['email'],
            'inquiry_message' => $data['message'],
            'source_url'  => $data['source_url'],
        ];

        // Send email
        try {
            // Local previews must never send notifications to real recipients.
            if (!app()->environment('local', 'testing')) {
                Mail::send('serendipity.villas::mail.inquiry', $mailData, function($m) use ($data, $wantsLayouts) {
                    $to = env('INQUIRY_TO', 'pietersfranken@gmail.com');
                    $subjectVilla = $data['villa_title'] ?: 'Villa';
                    $m->to($to)
                      ->replyTo($data['email'])
                      ->subject(($wantsLayouts ? 'Layout request: ' : 'Inquiry: ') . $subjectVilla);
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to send inquiry email: '.$e->getMessage());
        }

        if ($wantsLayouts) {
            $grant = LayoutDownloadAccess::current()->grant((int) $villa->id,
                (int) config('serendipity.villas::layouts.signed_url_ttl_minutes', 30));
            $downloadUrl = url('/download/villa-layouts/'.$villa->id.'/'.$grant['signature'])
                .'?'.http_build_query(['expires' => $grant['expires']]);

            return [
                '#inquiryResult' => $this->renderPartial('inquiry/layouts-ready', [
                    'downloadUrl' => $downloadUrl,
                    'villaTitle' => $villa->title,
                ]),
                'layoutDownloadUrl' => $downloadUrl,
            ];
        }

        return [
            '#inquiryResult' => '<div class="sl-lead">Thank you — our concierge will contact you shortly.</div>'
        ];
    }
}

<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Serendipity\Villas\Models\Inquiry;

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

        // Attach villa context if available on the page (do not overwrite posted values)
        $villa = $this->page['villa'] ?? null;
        $data['villa_id'] = $data['villa_id'] ?? ($villa->id ?? null);
        $data['villa_title'] = $data['villa_title'] ?? ($villa->title ?? null);
        $data['source_url'] = request()->fullUrl();

        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'message' => 'required|min:10',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new \ValidationException($validator);
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
            Mail::send('serendipity.villas::mail.inquiry', $mailData, function($m) use ($data) {
                $to = env('INQUIRY_TO', 'pietersfranken@gmail.com');
                $subjectVilla = $data['villa_title'] ?: 'Villa';
                $m->to($to)
                  ->replyTo($data['email'])
                  ->subject('Inquiry: ' . $subjectVilla);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send inquiry email: '.$e->getMessage());
        }

        return [
            '#inquiryResult' => '<div class="sl-lead">Thank you — our concierge will contact you shortly.</div>'
        ];
    }
}


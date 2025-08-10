<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'message' => 'required|min:10',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new \ValidationException($validator);
        }

        // For now, log the inquiry; we can switch to a real mailbox/CRM later
        Log::info('Villa Inquiry', $data);

        return [
            '#inquiryResult' => '<div class="sl-lead">Thank you — our concierge will contact you shortly.</div>'
        ];
    }
}


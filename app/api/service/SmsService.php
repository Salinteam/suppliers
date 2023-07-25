<?php

namespace App\api\service;

class SmsService
{

    public function sendPattern(int $template_id, string $mobile, array $parameters): void
    {

        $parameter_array = [];
        foreach ($parameters as $key => $value) {
            $parameter_array[] = ["name" => $key, "value" => $value];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => BASE_URL_SMS_IR,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                "Mobile" => $mobile,
                "TemplateId" => $template_id,
                "Parameters" => $parameter_array
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: text/plain', 'x-api-key:' . TOKEN_SMS_IR]
        ]);
        curl_exec($curl);
        curl_close($curl);

    }

}
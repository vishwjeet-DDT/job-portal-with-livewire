<?php

namespace App\Services;


class EmailTemplateService
{
    function parseTemplate($template, $data = [])
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }
}

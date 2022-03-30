<?php

namespace CloudConvert\Handler;

use CloudConvert\Models\Job;

class SignedUrlBuilder
{


    public function createFromJob(string $base, string $signingSecret, Job $job, string $cacheKey = null): string
    {

        $json = json_encode($job->getPayload());
        $base64EncodedJob = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');

        $url = $base . '?job=' . $base64EncodedJob;

        if($cacheKey) {
            $url .= '&cache_key=' . $cacheKey;
        }

        $signature = hash_hmac('sha256', $url, $signingSecret);

        $url .= '&s=' . $signature;

        return $url;

    }

}
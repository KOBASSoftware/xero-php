<?php

namespace XeroPHP\Remote\Exception;

use XeroPHP\Remote\Exception;
use XeroPHP\Remote\Response;

class RateLimitExceededException extends Exception
{
    protected $message = 'The API rate limit for your organisation/application pairing has been exceeded.';

    protected $code = Response::STATUS_RATE_LIMIT_EXCEEDED;

    protected $x_rate_limit_problem = null;
    protected $api_limit_hit_date = null;
    protected $api_next_request_allowed_date = null;
    protected $api_next_request_allowed_display_date = null;

    public const X_RATE_LIMIT_PROBLEM_MINUTE = 'Minute';
    public const X_RATE_LIMIT_PROBLEM_DAILY = 'Daily';

    public function __construct(array $response_header = []) {

        if(!empty($response_header)) {

            foreach ($response_header as $header) {
                if (strpos($header, 'X-Rate-Limit-Problem') !== false) {
                    $this->x_rate_limit_problem = trim(substr($header, strpos($header, ':') + 1));
                }
            }
            $this->message .= ' ' . $this->x_rate_limit_problem;

            $this->api_limit_hit_date            = new \DateTime();
            $this->api_next_request_allowed_date = clone $this->api_limit_hit_date;

            if ($this->x_rate_limit_problem == RateLimitExceededException::X_RATE_LIMIT_PROBLEM_MINUTE) {

                $this->api_next_request_allowed_date->add(new \DateInterval('PT120S'));
                $this->api_next_request_allowed_display_date = $this->api_next_request_allowed_date->format('G:i');

            } elseif ($this->x_rate_limit_problem == RateLimitExceededException::X_RATE_LIMIT_PROBLEM_DAILY) {

                $this->api_next_request_allowed_date = $this->api_limit_hit_date->add(new \DateInterval('PT12H'));
                $this->api_next_request_allowed_display_date = $this->api_next_request_allowed_date->format('M jS G:i');
            }
        }
    }

    public function getXRateLimitProblem(): ?string
    {
        return $this->x_rate_limit_problem;
    }

    public function setXRateLimitProblem(string $x_rate_limit_problem): RateLimitExceededException
    {
        $this->x_rate_limit_problem = $x_rate_limit_problem;
        return $this;
    }

    public function getApiLimitHitDate(): \DateTime
    {
        return $this->api_limit_hit_date;
    }

    public function setApiLimitHitDate(\DateTime $api_limit_hit_date): RateLimitExceededException
    {
        $this->api_limit_hit_date = $api_limit_hit_date;
        return $this;
    }

    public function getApiNextRequestAllowedDate(): \DateTime
    {
        return $this->api_next_request_allowed_date;
    }

    public function setApiNextRequestAllowedDate(\DateTime $api_next_request_allowed_date): RateLimitExceededException
    {
        $this->api_next_request_allowed_date = $api_next_request_allowed_date;

        if ($this->x_rate_limit_problem == RateLimitExceededException::X_RATE_LIMIT_PROBLEM_MINUTE) {
            $this->setApiNextRequestAllowedDisplayDate($this->api_next_request_allowed_date->format('G:i'));
        } else {
            $this->setApiNextRequestAllowedDisplayDate($this->api_next_request_allowed_date->format('M jS G:i'));
        }

        return $this;
    }

    public function getApiNextRequestAllowedDisplayDate(): string
    {
        return $this->api_next_request_allowed_display_date;
    }
}

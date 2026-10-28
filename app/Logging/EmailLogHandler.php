<?php

namespace App\Logging;

use Illuminate\Support\Facades\Mail;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class EmailLogHandler extends AbstractProcessingHandler
{
    /**
     * @var string[]
     */
    protected array $recipients = [];

    /**
     * @param  string[]  $recipients
     */
    public function __construct(array $recipients = [], int|string|Level $level = Level::Critical, bool $bubble = true)
    {
        $this->recipients = $recipients ?: array_values(array_filter(array_map('trim', explode(',', (string) env('LOGGING_EMAIL_RECIPIENTS', '')))));

        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if ($this->recipients === []) {
            return;
        }

        $subject = sprintf('%s %s', config('app.name', 'Laravel'), ucfirst($record->level->getName()));
        $message = $record->formatted ?? $record->message;

        Mail::raw($message, function ($mail) use ($subject) {
            $mail->to($this->recipients)
                ->subject($subject)
                ->from(
                    config('mail.from.address', 'no-reply@example.com'),
                    config('mail.from.name', config('app.name', 'Laravel'))
                );
        });
    }
}

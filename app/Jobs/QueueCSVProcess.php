<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Branch;

class QueueCSVProcess implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $header;
    /**
     * Create a new job instance.
     */
    public function __construct($data , $header)
    {
        $this->data = $data;
        $this->header = $header;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $header = $this->header;

        // foreach ($this->data['csvData'] as $data) {
        //     $keyValueData = array_combine($this->data['header'], $data);
        //     $branch = new Branch;
        //     $branch->branch_name = isset($keyValueData['number']) ? utf8_encode($keyValueData['number']) : null;
        //     $branch->save();
        // }
        foreach($this->data as $data){
            $keyValueData = array_combine($header , $data);
            // $number = utf8_encode($keyValueData['Academic Year']);
            $number = utf8_encode($keyValueData['number']);
            // $number = utf8_encode($keyValueData['Program']);
            // $number = mb_check_encoding($keyValueData['Program'], 'UTF-8');

            // $encoding = mb_detect_encoding($number, 'UTF-8, ISO-8859-1, WINDOWS-1252, WINDOWS-1251', true);
            // if ($encoding != 'UTF-8') {
            //     $string = iconv($encoding, 'UTF-8//IGNORE', $row[1]);
            // }


            $branch = new Branch;
            $branch->branch_name = $number;
            $branch->save();
        }
    }
    public function failed(Throwable $exception): void
    {
        // Send user notification of failure, etc...
    }
}

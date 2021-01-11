<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class BatchProc1 implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $id;

    private int $sleep_msec;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id, int $sleep_msec = 1000)
    {
        $this->id = $id;

        $this->sleep_msec = $sleep_msec;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            Log::debug("Batch Process 1:{$this->id} cancelled.");
            return;
        }

        Log::debug("Batch Process 1:{$this->id} begin.");

        if ($this->id === 0) {
            throw new \Exception("IDがおかしい");
        }
        // ini_set("memory_limit", "200M");

        if ($this->id === -1) {
            // 処理の追加
            for ($i = 10; $i < 100; $i++) {
                $this->batch()->add(new BatchProc1($i));
            }
        }
        
        Log::debug("memory_limit=" . ini_get("memory_limit"));

        usleep($this->sleep_msec * 1000);

        Log::debug("Batch Process 1:{$this->id} Executed.");
    }
}

<?php

namespace App\Console\Commands;

use Throwable;
use App\Jobs\BatchProc1;
use App\Jobs\BatchProc2;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ExampleBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch example';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $batch = $this->execBatch();
        $this->info("Batch ID is {$batch->id}");

        return $this->monitorBatch($batch->id);
    }


    private function execBatch()
    {
        $batch = Bus::batch([
            [
                new BatchProc1(1, 5000),
                new BatchProc1(2, 2000),
                new BatchProc1(-1),
                new BatchProc1(3, 3000),
                new BatchProc1(4, 4000),
            ],
            [
                new BatchProc2(5, 1000),
                new BatchProc2(6, 1000),
                // new BatchProc1(0),
                new BatchProc2(7, 2000),
                new BatchProc2(8, 3000),
            ],
        ])
        ->then(function (Batch $batch) {
            Log::debug("then:すべてのジョブが成功して完了した");
            // Log::debug(var_export($batch, true));  <-- var_export で $batch を表示できない（理由は不明だが再起ループに陥る）
            Log::debug("Batch ID is {$batch->id}");
        })
        ->catch(function (Batch $batch, Throwable $e) {
            Log::debug("catch:バッチジョブの失敗をはじめて検出した");
            // Log::debug(var_export($batch, true));
            // Log::debug(var_export($e, true));
        })
        ->finally(function (Batch $batch) {
            Log::debug("finally:バッチの実行を終了した");
            // Log::debug(var_export($batch, true));
        })
        ->name("Example Batch")
        ->dispatch();

        return $batch;
    }

    private function monitorBatch($id)
    {

        while (true) {
            $b = Bus::findBatch($id);
            $this->showBatchInfo($b);

            if ($b->finished()) {
                break;
            }
            sleep(1);
        }
    
        $b = Bus::findBatch($id);

        if ($b->cancelled()) {
            $this->showBatchInfo($b);

            if ($b->hasFailures()) {
                $this->info("バッチはエラーにより中断しました。");
                return 255;
            } else {
                $this->info("バッチは中断要求により中断しました。");
                return 254;
            }
        }

        return 0;
    }

    private function showBatchInfo(Batch $batch)
    {
        $this->info("---------------------------------");
        $this->info("Batch ID: " . $batch->id);
        $this->info("Batch Name: " . $batch->name);
        $this->info("Progress: " . $batch->progress() . "%");
        $this->info("Total Jobs: " . $batch->totalJobs);
        $this->info("Pending Jobs: " . $batch->pendingJobs);
        $this->info("Failed Jobs: " . $batch->failedJobs);
        if ($batch->failedJobs > 0) {
            $this->error("Failed Job Ids: " . var_export($batch->failedJobIds, true));
        }
        $this->info("Processed Jobs: " . $batch->processedJobs());
    }
}

<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Bus\BatchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class ExampleBatchController extends Controller
{
    private BatchRepository $batchRepository;

    public function __construct(BatchRepository $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    public function list(Request $request)
    {
        $n = $request->n ?? 50;
        $before = $request->before ?? null;
        $list = $this->batchRepository->get($n, $before);
        
        return $list;
    }


    public function status($batchId)
    {
        return Bus::findBatch($batchId);
    }
}

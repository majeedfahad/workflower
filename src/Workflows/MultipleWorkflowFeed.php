<?php

namespace Majeedfahad\Workflower\Workflows;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Majeedfahad\Workflower\Contracts\Workflowable;

abstract class MultipleWorkflowFeed extends WorkflowFeed
{
    protected Collection $workflowers;

    public function feed()
    {
        if (DB::table('workflow_updates')->where('class', pathinfo(__FILE__, PATHINFO_FILENAME))->exists()) {
            echo "⚠️ Workflow update already applied." . PHP_EOL;
            return;
        }

        if ($this->workflowers->isEmpty()) {
            echo "⚠️ No workflowers found." . PHP_EOL;
            return;
        }

        $appliedCount = 0;

        $this->workflowers->each(function (Workflowable $workflower) use (&$appliedCount) {
            DB::beginTransaction();
            try {
                $this->updateWorkflow($workflower);
                DB::commit();
                $appliedCount++;
                echo "✅ Applied workflow update for workflower #{$workflower->id} ({$workflower->name})" . PHP_EOL;
            } catch (\Throwable $e) {
                DB::rollBack();
                echo "❌ Failed for workflower #{$workflower->id} ({$workflower->name}): {$e->getMessage()}" . PHP_EOL;
                Log::error("[WorkflowUpdate] Failed for workflower #{$workflower->id} ({$workflower->name}): " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });

        echo "🎯 Total workflower updated: {$appliedCount}" . PHP_EOL;
    }

    public function singleFeed(Workflowable $workflower)
    {
        DB::beginTransaction();
        $this->updateWorkflow($workflower);
        DB::commit();
    }

    abstract protected function updateWorkflow(Workflowable $workflower): void;
}
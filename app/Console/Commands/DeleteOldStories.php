<?php

namespace App\Console\Commands;

use App\Models\User\Stories;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldStories extends Command
{
    protected $signature = 'stories:delete-old';
    protected $description = 'Delete stories older than 24 hours';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $stories = Stories::where('created_at', '<', Carbon::now()->subDay())->get();

        foreach ($stories as $story) {
            Storage::disk('public')->delete($story->file_path);
            $story->delete();
        }

        $this->info('Old stories deleted successfully');
    }
}

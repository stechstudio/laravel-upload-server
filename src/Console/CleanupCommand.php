<?php

namespace STS\UploadServer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use STS\UploadServer\Storage\PartialFile;

class CleanupCommand extends Command
{
    /** @var string */
    protected $signature = 'uploads:cleanup {-I|interval=}';

    /** @var string */
    protected $description = 'Cleans up abandoned .part files';

    public function handle()
    {
        PartialFile::all()
            ->filter(fn(PartialFile $file) => $file->getMTime() < $this->timestamp())
            ->each(fn(PartialFile $file) => $this->remove($file))
            ->tap(fn($result) => $this->info($this->resultMessage($result->count())));
    }

    protected function remove(PartialFile $file)
    {
        $this->comment('> ' . $file->getRelativePath());
        $file->delete();
    }

    protected function timestamp(): int
    {
        return strtotime(
            Str::start($this->interval(), '-')
        );
    }

    protected function interval()
    {
        return $this->option('interval') ?: config('upload-server.cleanup_interval');
    }

    protected function resultMessage($count)
    {
        return $count == 0
            ? "No abandoned partial uploads found"
            : "Cleaned up $count abandoned partial " . Str::plural('upload', $count);
    }
}

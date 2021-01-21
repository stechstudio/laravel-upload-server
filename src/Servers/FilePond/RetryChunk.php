<?php

namespace STS\UploadServer\Servers\FilePond;

use Illuminate\Http\Request;
use STS\UploadServer\Events\ChunkedUploadRetrying;
use STS\UploadServer\Servers\AbstractStep;
use STS\UploadServer\Storage\File;
use STS\UploadServer\Storage\PartialFile;

class RetryChunk extends AbstractStep
{
    use PayloadHelper;

    protected $event = ChunkedUploadRetrying::class;

    public static function handles(Request $request): bool
    {
        return $request->method() == 'HEAD'
            && $request->has('patch');
    }

    public function handle()
    {
        // The client is attempting to resume a file that it believes was already started
        // and should exist on the server. If we can find a matching partial file, we're
        // good and we'll resume that.
        if(PartialFile::exists($this->patch())) {
            $this->file = PartialFile::find($this->patch());
            return;
        }

        // Sometimes the client actually did upload the last chunk but didn't receive
        // a successful confirmation, and is retrying. If we can find a matching completed
        // file we'll use it, and will tell the client that it's all good.
        if(File::exists($this->patch())) {
            $this->file = File::find($this->patch());
            return;
        }

        // If we found no matching file at all, I'm not really sure what happened.
        // Maybe the client stalled, and sat for hours before hitting retry, and
        // the partial file was already cleaned up? In any event, we have to treat
        // it as a new upload that is just starting.
        $this->file = PartialFile::initialize($this->patch());
    }

    public function percentComplete(): int
    {
        return 0;
    }

    public function response()
    {
        return response('', 200, ['Upload-Offset' => $this->file->getSize()]);
    }
}

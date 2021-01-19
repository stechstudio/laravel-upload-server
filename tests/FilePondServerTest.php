<?php

namespace STS\UploadServer\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use STS\UploadServer\Results\Concerns\TracksProgressInSession;
use STS\UploadServer\Results\RetryChunkedUpload;
use STS\UploadServer\Results\StartingChunkedUpload;
use STS\UploadServer\Results\ReceivedChunk;
use STS\UploadServer\Servers\FilePondServer;
use STS\UploadServer\UploadProgress;

class FilePondServerTest extends TestCase
{
    use TracksProgressInSession;

    public function test_it_starts_new_chunked_uploads()
    {
        $request = Request::create('', 'POST',[],[],[],[
            'HTTP_UPLOAD_LENGTH' => 20
        ]);

        $server = new FilePondServer($request);

        $this->assertTrue($server->isStartingChunkedUpload());
        $this->assertFalse($server->isRetryingChunkedUpload());
        $this->assertFalse($server->isChunk());
        $this->assertFalse($server->isSingleUpload());

        /** @var StartingChunkedUpload $result */
        $result = $server->receive();

        $this->assertInstanceOf(StartingChunkedUpload::class, $result);
        $this->assertEquals(20, $result->expectedSize());
        $this->assertFileExists($result->file()->getRealPath());
        $this->assertEquals(0, filesize($result->file()->getRealPath()));
    }

    public function test_it_saves_first_chunk()
    {
        $request = Request::create('', 'PATCH',['patch' => $id = Str::uuid()],[],[],[
            'HTTP_UPLOAD_LENGTH' => 20,
            'HTTP_UPLOAD_NAME' => 'test.txt',
            'HTTP_UPLOAD_OFFSET' => 0
        ], 'hello there');

        $server = new FilePondServer($request);

        // We don't consider the request a valid chunk unless there is a part file on disk
        $this->assertFalse($server->isChunk());

        $server->initializeChunkFile($id);

        // Now we should be in good shape
        $this->assertFalse($server->isStartingChunkedUpload());
        $this->assertFalse($server->isRetryingChunkedUpload());
        $this->assertTrue($server->isChunk());
        $this->assertFalse($server->isSingleUpload());

        $this->startSession();
        $this->saveProgress(UploadProgress::initialize($id, 20, $server->fullChunkPath($id)));

        /** @var ReceivedChunk $result */
        $result = $server->receive();

        $this->assertInstanceOf(ReceivedChunk::class, $result);
        $this->assertEquals(11, $result->file()->getSize());
    }

    public function test_it_supports_chunk_retry()
    {
        $request = Request::create('', 'HEAD',['patch' => $id = Str::uuid()]);

        $server = new FilePondServer($request);

        // We don't consider the request a retry unless we have verified the part file on disk
        $this->assertFalse($server->isRetryingChunkedUpload());

        $server->initializeChunkFile($id);

        // Now we should be in good shape
        $this->assertFalse($server->isStartingChunkedUpload());
        $this->assertTrue($server->isRetryingChunkedUpload());
        $this->assertFalse($server->isChunk());
        $this->assertFalse($server->isSingleUpload());

        // Let's put some content in the file
        file_put_contents($server->fullChunkPath($id), 'hello there');

        $this->startSession();
        $this->saveProgress(UploadProgress::restore([
            'id' => $id,
            'percent' => 55,
            'currentSize' => 11,
            'expectedSize' => 20,
            'path' => $server->fullChunkPath($id)
        ]));

        /** @var RetryChunkedUpload $result */
        $result = $server->receive();

        $this->assertInstanceOf(RetryChunkedUpload::class, $result);
    }
}

<?php

namespace STS\UploadServer\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use STS\UploadServer\Upload;
use STS\UploadServer\UploadServerFacade;

/**
 * See https://pqina.nl/filepond/docs/patterns/api/server/
 */
class FilePondTest extends TestCase
{
    protected $route;

    public function setUp(): void
    {
        parent::setUp();

        $this->route = route('filepond-server');
    }

    public function test_it_can_upload_simple()
    {
        $response = $this->post($this->route, [
            'file' => UploadedFile::fake()
                ->createWithContent('Simple.txt', 'this is a simple upload')
        ]);
        
        $upload = Upload::find($response->getContent());

        $this->assertEquals(23, filesize($upload->path()));
        $this->assertEquals('Simple.txt', $upload->getClientOriginalName());
    }

    public function test_it_can_upload_with_unexpected_name_and_array_wrapped()
    {
        $response = $this->post($this->route, [
            'youAintGonnaKnowThisOne[]' => UploadedFile::fake()
                ->createWithContent('Simple.txt', 'this is a simple upload')
        ]);

        $upload = UploadServerFacade::retrieve($response->getContent());

        $this->assertEquals(23, filesize($upload->path()));
        $this->assertEquals('Simple.txt', $upload->getClientOriginalName());
    }

    public function test_it_can_begin_chunked_upload()
    {
        $response = $this->post($this->route, [], [
            'Upload-Length' => 19
        ]);

        $fileId = $response->getContent();

        $upload = Upload::findPart($fileId);

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertTrue(Str::isUuid($upload->id()));
        $this->assertEquals(0, filesize($upload->path()));

        return $fileId;
    }

    /**
     * @depends test_it_can_begin_chunked_upload
     */
    public function test_it_can_upload_chunk($fileId)
    {
        $this->startSession();

        $upload = Upload::findPart($fileId);

        // Send a chunk
        $this->patch($this->route, ['patch' => $fileId], [
            'Upload-Offset' => 0,
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'application/offset+octet-stream'
        ], "1st chunk-");

        $this->assertEquals(10, filesize($upload->path()));

        return [$fileId, $this->app['session']->get('upload-server')];
    }

    /**
     * @depends test_it_can_upload_chunk
     */
    public function test_it_can_retry_chunk($payload)
    {
        [$fileId, $session] = $payload;

        $this->startSession();

        $this->app['session']->put('upload-server', $session);

        $response = $this->call('HEAD', $this->route, ['patch' => $fileId]);

        $response->assertHeader('Upload-Offset', 10);

        return [$fileId, $response->headers->get('Upload-Offset')];
    }

    /**
     * @depends test_it_can_retry_chunk
     */
    public function test_it_can_finish_chunked($payload)
    {
        [$fileId, $offset] = $payload;

        $this->startSession();

        // Send a chunk
        $response = $this->patch($this->route, ['patch' => $fileId], [
            'Upload-Offset' => $offset,
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'applic
            ation/offset+octet-stream'
        ], "2nd chunk");

        $upload = Upload::find($fileId);

        $this->assertEquals(19, filesize($upload->path()));
        $this->assertEquals('Chunked.txt', $upload->getClientOriginalName());
    }

}

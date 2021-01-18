<?php

namespace STS\UploadServer\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use STS\UploadServer\Upload;

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

    public function test_it_can_begin_chunked_upload()
    {
        $response = $this->post($this->route, [], [
            'Upload-Length' => 19
        ]);

        $serialized = $response->getContent();

        $upload = Upload::restore($serialized);

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertTrue(Str::isUuid($upload->id()));
        $this->assertEquals(0, filesize($upload->path()));

        return $serialized;
    }

    /**
     * @depends test_it_can_begin_chunked_upload
     */
    public function test_it_can_upload_chunk($serialized)
    {
        $this->startSession();

        $upload = Upload::restore($serialized);

        // Send a chunk
        $response = $this->patch($this->route, ['patch' => $serialized], [
            'Upload-Offset' => 0,
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'application/offset+octet-stream'
        ], "1st chunk-");

        $this->assertEquals(10, filesize($upload->path()));

        return $serialized;
    }

    /**
     * @depends test_it_can_upload_chunk
     */
    public function test_it_can_finish_chunked($serialized)
    {
        $this->startSession();

        // Send a chunk
        $response = $this->patch($this->route, ['patch' => $serialized], [
            'Upload-Offset' => 10,
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'applic
            ation/offset+octet-stream'
        ], "2nd chunk");

        $upload = Upload::restore($response->getContent());

        $this->assertEquals(19, filesize($upload->path()));
        $this->assertEquals('Chunked.txt', $upload->getClientOriginalName());
    }

    public function test_it_can_upload_simple()
    {
        $response = $this->post($this->route, [
            'file' => UploadedFile::fake()->createWithContent('Simple.txt', 'this is a simple upload')
        ]);

        $upload = Upload::restore($response->getContent());

        $this->assertEquals(23, filesize($upload->path()));
        $this->assertEquals('Simple.txt', $upload->getClientOriginalName());
    }
}

<?php

namespace STS\UploadServer\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use STS\UploadServer\Exceptions\InvalidChunkException;
use STS\UploadServer\Exceptions\InvalidUploadException;
use STS\UploadServer\Storage\File;
use STS\UploadServer\Storage\PartialFile;
use STS\UploadServer\Upload;

/**
 * See https://pqina.nl/filepond/docs/patterns/api/server/
 */
class FilePondHttpTest extends TestCase
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

        $upload = File::find($response->getContent());

        $this->assertEquals(23, filesize($upload->path()));
        $this->assertEquals('Simple.txt', $upload->getClientOriginalName());
    }

    public function test_it_can_upload_with_unexpected_name_and_array_wrapped()
    {
        $response = $this->post($this->route, [
            'youAintGonnaKnowThisOne[]' => UploadedFile::fake()
                ->createWithContent('Simple.txt', 'this is a simple upload')
        ]);

        $upload = File::find($response->getContent());

        $this->assertEquals(23, filesize($upload->path()));
        $this->assertEquals('Simple.txt', $upload->getClientOriginalName());
    }

    public function test_it_can_begin_chunked_upload()
    {
        $response = $this->post($this->route, [], [
            'Upload-Length' => 19
        ]);

        $fileId = $response->getContent();

        $upload = PartialFile::find($fileId);

        $this->assertInstanceOf(UploadedFile::class, $upload);
        $this->assertTrue(Str::isUuid($upload->id()));
        $this->assertEquals(0, filesize($upload->path()));

        return $fileId;
    }

    /**
     * @depends test_it_can_begin_chunked_upload
     */
    public function test_it_can_upload_chunk($fileId)
    {
        $upload = PartialFile::find($fileId);

        // Send a chunk
        $response = $this->patch($this->route, ['patch' => $fileId], [
            'Upload-Offset' => 0,
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'application/offset+octet-stream'
        ], "1st chunk-");

        $this->assertEquals("1st chunk-", $upload->get());

        return $fileId;
    }

    /**
     * @depends test_it_can_upload_chunk
     */
    public function test_it_can_retry_chunk($fileId)
    {
        $response = $this->call('HEAD', $this->route, ['patch' => $fileId]);

        $response->assertHeader('Upload-Offset', 10);

        return [$fileId, $response->headers->get('Upload-Offset')];
    }

    /**
     * @depends test_it_can_retry_chunk
     */
    public function test_it_cannot_use_invalid_offset($payload)
    {
        [$fileId, $offset] = $payload;

        $this->expectException(InvalidChunkException::class);

        // Send a chunk
        $response = $this->withoutExceptionHandling()->patch($this->route, ['patch' => $fileId], [
            'Upload-Offset' => $offset - 5, // Purposefully try to overwrite part of the existing chunk
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'applic
            ation/offset+octet-stream'
        ], "2nd chunk");
    }

    /**
     * @depends test_it_can_retry_chunk
     */
    public function test_it_can_finish_chunked($payload)
    {
        [$fileId, $offset] = $payload;

        // Send a chunk
        $response = $this->patch($this->route, ['patch' => $fileId], [
            'Upload-Offset' => $offset,
            'Upload-Name'   => 'Chunked.txt',
            'Upload-Length' => 19,
            'Content-Type'  => 'application/offset+octet-stream'
        ], "2nd chunk");

        $upload = File::find($fileId);

        $this->assertEquals(19, filesize($upload->path()));
        $this->assertEquals('Chunked.txt', $upload->getClientOriginalName());

        return $fileId;
    }

    /**
     * @depends test_it_can_finish_chunked
     */
    public function test_it_can_delete_uploaded_file($fileId)
    {
        $response = $this->delete($this->route, ['patch' => $fileId]);

        $this->assertFalse(File::exists($fileId));
    }
}

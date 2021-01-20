<?php

namespace STS\UploadServer\Tests;

use Illuminate\Support\Str;
use STS\UploadServer\File;

class FileTest extends TestCase
{
    public function test_it_can_initialize_empty_partial()
    {
        $file = File::initializePartial(Str::uuid());

        $this->assertFileExists($file->getRealPath());
        $this->assertEquals(0, $file->getSize());
    }

    public function test_it_can_find_partial_file()
    {
        file_put_contents(File::fullPartialPath($id = Str::uuid()), "I don't mean to pry");

        $file = File::findPartial($id);

        $this->assertFileExists($file->getRealPath());
        $this->assertEquals(19, $file->getSize());
    }

    public function test_it_can_create_from_disk()
    {
        $path = sys_get_temp_dir() . "/" . md5(time()) . ".txt";
        file_put_contents($path, "My way's not very sportsman-like");

        $file = File::fromPath($path);

        $this->assertFileExists($file->getRealPath());
        $this->assertEquals(32, $file->getSize());
    }

    public function test_it_can_append_from_stream()
    {
        file_put_contents(File::fullPartialPath($id = Str::uuid()), "We need a miracle. ");

        $file = File::findPartial($id);

        $path = sys_get_temp_dir() . "/" . md5(time()) . ".txt";
        file_put_contents($path, "It's very important.");

        $incoming = File::fromPath($path);

        $file->appendFile($incoming);

        $this->assertEquals("We need a miracle. It's very important.", $file->get());
    }

    public function test_it_can_append_content()
    {
        file_put_contents(File::fullPartialPath($id = Str::uuid()), "No. ");

        $file = File::findPartial($id);

        $file->appendContent('To the pain.');

        $this->assertEquals("No. To the pain.", $file->get());
    }
}

<?php
use Phifty\FileUtils as FileUtils;  # alias \Phifty\FileUtils class to FileUtils

class FileUtilsTest extends \PHPUnit\Framework\TestCase
{
    public function testJoin()
    {
        $path1 = FileUtils::path_join( 'path' , 'path2' );
        # this works too.
        # $path1 = Phifty\FileUtils::path_join( 'path' , 'path2' );
        $this->assertEquals( $path1 , 'path' . DIRECTORY_SEPARATOR . 'path2' );
    }

    public function testMimetype()
    {
        $mimetype = FileUtils::mimetype('tests/data/404.png');
        $this->assertEquals('image/png',$mimetype);

        $mimetype = FileUtils::mimetype('tests/data/email.xlsx');
        // $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',$mimetype);

        $mimetype = FileUtils::mimetype('tests/data/street_names.txt');
        $this->assertEquals('text/plain',$mimetype);
    }

    public function testRelativePath()
    {
        $path2 = FileUtils::relative_path( __FILE__ );
        $this->assertNotNull( $path2 );
        $this->assertEquals( $path2 , 'tests/Phifty/FileUtilsTest.php' );
    }

    public function testexpand()
    {
        $paths = FileUtils::expand_path( '/path/{to,to2,to3,foo,bar}/end' );
        $this->assertEquals( count($paths) , 5 );

        $paths = FileUtils::expand_path( '/path/foo/end' );
        $this->assertEquals( count($paths) , 1 );

    }
}


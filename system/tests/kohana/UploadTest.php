<?php

/**
 * Tests Kohana upload class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.upload
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */
class Kohana_UploadTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_size()
	 * 
	 * @return array
	 */
	public function provider_size()
	{
		return [
			// $field, $bytes, $environment, $expected
			[
				'unit_test', 
				5, 
				['_FILES' => ['unit_test' => ['error' => UPLOAD_ERR_INI_SIZE]]], 
				FALSE
			],
			[
				'unit_test', 
				5, 
				['_FILES' => ['unit_test' => ['error' => UPLOAD_ERR_NO_FILE]]], 
				TRUE
			],
			[
				'unit_test', 
				'6K', 
				['_FILES' => [
					'unit_test' => [
						'error' => UPLOAD_ERR_OK,
						'name' => 'Unit_Test File',
						'type' => 'image/png',
						'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
						'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
						]
					]
				], 
				TRUE
			],
			[
				'unit_test', 
				'1B', 
				['_FILES' => [
						'unit_test' => [
							'error' => UPLOAD_ERR_OK,
							'name' => 'Unit_Test File',
							'type' => 'image/png',
							'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
							'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
						]
					]
				], 
				FALSE
			],
		];
	}

	/**
	 * Tests Upload::size
	 *
	 * @test
	 * @dataProvider provider_size
	 * @covers upload::size
	 * @param string $field the files field to test
	 * @param string $bytes valid bite size
	 * @param array $environment set the $_FILES array
	 * @param bool $expected what to expect
	 */
	public function test_size($field, $bytes, $environment, $expected)
	{
		$this->setEnvironment($environment);

		$this->assertSame($expected, Upload::size($_FILES[$field], $bytes));
	}

	/**
	 * size() should throw an exception of the supplied max size is invalid
	 *
	 * @test
	 * @covers upload::size
	 */
	public function test_size_throws_exception_for_invalid_size()
	{
		$this->expectException('Kohana_Exception');
		$this->setEnvironment([
			'_FILES' => [
				'unit_test' => [
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			]
		]);

		Upload::size($_FILES['unit_test'], '1DooDah');
	}

	/**
	 * Provides test data for test_valid()
	 *
	 * @return array
	 */
	public function provider_valid()
	{
		return [
			[
				TRUE,
				[
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'error' => UPLOAD_ERR_OK,
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'name' => 'Unit_Test File',
					'error' => UPLOAD_ERR_OK,
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			],
			[
				FALSE,
				[
					'error' => UPLOAD_ERR_OK,
					'name' => 'Unit_Test File',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
				]
			],
			
		];
	}

	/**
	 * Test Upload::valid
	 *
	 * @test
	 * @dataProvider provider_valid
	 * @covers Upload::valid
	 */
	public function test_valid($expected, $file)
	{
		$this->setEnvironment([
			'_FILES' => [
				'unit_test' => $file,
			],
		]);

		$this->assertSame($expected, Upload::valid($_FILES['unit_test']));
	}

	/**
	 * Tests Upload::type
	 *
	 * @test
	 * @covers Upload::type
	 */
	public function test_type()
	{
		$this->setEnvironment([
			'_FILES' => [
				'unit_test' => [
					'error' => UPLOAD_ERR_OK,
					'name' => 'github.png',
					'type' => 'image/png',
					'tmp_name' => Kohana::find_file('tests', 'test_data/github', 'png'),
					'size' => filesize(Kohana::find_file('tests', 'test_data/github', 'png')),
				]
			]
		]);
		
		$this->assertTrue(Upload::type($_FILES['unit_test'], ['jpg', 'png', 'gif']));

		$this->assertFalse(Upload::type($_FILES['unit_test'], ['docx']));
	}

	/**
	 * Provider for test_image_size_rejects_svg_and_non_images
	 *
	 * @return array of [$contents], keyed by description
	 */
	public static function provider_image_size_rejects()
	{
		return [
			'SVG with dimensions' => [
				'<svg xmlns="http://www.w3.org/2000/svg" width="100" height="50"><script>alert(1)</script></svg>',
			],
			'SVG with XML declaration' => [
				'<?xml version="1.0" encoding="UTF-8"?>'."\n"
				.'<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" onload="alert(1)"></svg>',
			],
			'plain text' => [
				'not an image',
			],
		];
	}

	/**
	 * PHP 8.5's getimagesize() recognises SVG. Upload::image() must keep
	 * rejecting SVG (and other non-images) as it does on PHP 8.4, because SVG
	 * can carry script.
	 *
	 * @test
	 * @dataProvider provider_image_size_rejects
	 * @covers Upload::_image_size
	 * @see https://github.com/hospicedev/konine/issues/44
	 *
	 * @param string $contents file contents
	 */
	public function test_image_size_rejects_svg_and_non_images($contents)
	{
		$file = tempnam(sys_get_temp_dir(), 'konine_upload_');
		file_put_contents($file, $contents);

		try
		{
			$this->assertFalse(Kohana_UploadTest_TestableUpload::image_size($file));
		}
		finally
		{
			unlink($file);
		}
	}

	/**
	 * Raster images are still accepted, with their dimensions.
	 *
	 * @test
	 * @covers Upload::_image_size
	 */
	public function test_image_size_returns_size_of_raster_image()
	{
		$file = Kohana::find_file('tests', 'test_data/github', 'png');
		$expected = getimagesize($file);

		$size = Kohana_UploadTest_TestableUpload::image_size($file);

		$this->assertIsArray($size);
		$this->assertSame(IMAGETYPE_PNG, $size[2]);
		$this->assertSame([$expected[0], $expected[1]], [$size[0], $size[1]]);
		$this->assertGreaterThan(0, $size[0]);
	}

	/**
	 * A file that cannot be read is rejected rather than raising an error.
	 *
	 * @test
	 * @covers Upload::_image_size
	 */
	public function test_image_size_rejects_missing_file()
	{
		$this->assertFalse(Kohana_UploadTest_TestableUpload::image_size('/nonexistent/konine/upload.png'));
	}
}

/**
 * Exposes the protected image size check, because Upload::image() itself
 * requires a real HTTP upload (is_uploaded_file()).
 */
class Kohana_UploadTest_TestableUpload extends Upload {

	/**
	 * @param  string  $filename
	 * @return array|FALSE
	 */
	public static function image_size($filename)
	{
		return static::_image_size($filename);
	}

}

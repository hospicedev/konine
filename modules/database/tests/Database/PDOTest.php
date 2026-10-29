<?php

/**
 * Test case for Kohana_Database_PDO and the query builder, run against an
 * in-memory SQLite database.
 *
 * @package    Kohana/Database
 * @group      kohana
 * @group      kohana.database
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_Database_PDOTest extends Unittest_TestCase
{
	/**
	 * @var Database_PDO
	 */
	protected $db;

	/**
	 * Creates a fresh in-memory database with a small "people" table.
	 *
	 * No charset is configured: Database_PDO::set_charset() issues
	 * "SET NAMES", which SQLite does not understand.
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->db = Database::instance('unittest_sqlite', [
			'type'         => 'PDO',
			'connection'   => [
				'dsn'        => 'sqlite::memory:',
				'persistent' => FALSE,
			],
			'table_prefix' => '',
			'identifier'   => '"',
			'caching'      => FALSE,
		]);

		$this->db->query(NULL, 'CREATE TABLE "people" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "name" TEXT NOT NULL, "age" INTEGER)');
		$this->db->query(Database::INSERT, "INSERT INTO \"people\" (\"name\", \"age\") VALUES ('alice', 30), ('bob', 25), ('carol', 35)");
	}

	public function tearDown(): void
	{
		$this->db->disconnect();

		$this->db = NULL;

		parent::tearDown();
	}

	/**
	 * Provides test data for test_compile(). Queries are built inside the
	 * test so the builder methods count towards its coverage.
	 *
	 * @return array
	 */
	public function provider_compile()
	{
		return [
			[
				'SELECT "id", "name" FROM "people" WHERE "age" > 25 AND "name" IS NOT NULL OR "id" IN (1, 2) ORDER BY "name" DESC LIMIT 2 OFFSET 1',
				function () {
					return DB::select('id', 'name')
						->from('people')
						->where('age', '>', 25)
						->and_where('name', '!=', NULL)
						->or_where('id', 'IN', [1, 2])
						->order_by('name', 'desc')
						->limit(2)
						->offset(1);
				},
			],
			[
				'SELECT * FROM "people" WHERE "name" = \'bob\'',
				function () {
					return DB::select()->from('people')->where('name', '=', ':name')->param(':name', 'bob');
				},
			],
			[
				'INSERT INTO "people" ("name", "age") VALUES (\'dave\', 40), (\'o\'\'neil\', NULL)',
				function () {
					return DB::insert('people', ['name', 'age'])->values(['dave', 40])->values(["o'neil", NULL]);
				},
			],
			[
				'UPDATE "people" SET "age" = 31, "name" = \'Alice\' WHERE "name" = \'alice\'',
				function () {
					return DB::update('people')->set(['age' => 31, 'name' => 'Alice'])->where('name', '=', 'alice');
				},
			],
			[
				'DELETE FROM "people" WHERE "age" BETWEEN 20 AND 30',
				function () {
					return DB::delete('people')->where('age', 'BETWEEN', [20, 30]);
				},
			],
			[
				'SELECT * FROM "people" WHERE "age" > 20 AND ("name" = \'alice\' OR "name" = \'bob\')',
				function () {
					return DB::select()
						->from('people')
						->where('age', '>', 20)
						->and_where_open()
						->where('name', '=', 'alice')
						->or_where('name', '=', 'bob')
						->and_where_close();
				},
			],
			[
				'SELECT * FROM "people"',
				function () {
					return DB::select()->from('people')->where_open()->where_close_empty();
				},
			],
			[
				'SELECT "people"."name" AS "n", COUNT(*) AS "total" FROM "people" AS "p"',
				function () {
					return DB::select(['people.name', 'n'], [DB::expr('COUNT(*)'), 'total'])->from(['people', 'p']);
				},
			],
			[
				'SELECT * FROM "people" WHERE "id" IN (NULL)',
				function () {
					return DB::select()->from('people')->where('id', 'IN', []);
				},
			],
			[
				'SELECT "name" FROM "people" WHERE "id" IN (SELECT "id" FROM "people" WHERE "age" > 30)',
				function () {
					return DB::select('name')
						->from('people')
						->where('id', 'IN', DB::select('id')->from('people')->where('age', '>', 30));
				},
			],
		];
	}

	/**
	 * @test
	 * @covers Database_Query::compile
	 * @covers Database_Query::param
	 * @covers Database_Query_Builder::_compile_conditions
	 * @covers Database_Query_Builder::_compile_set
	 * @covers Database_Query_Builder::_compile_order_by
	 * @covers Database_Query_Builder_Where::where
	 * @covers Database_Query_Builder_Where::and_where
	 * @covers Database_Query_Builder_Where::or_where
	 * @covers Database_Query_Builder_Where::where_open
	 * @covers Database_Query_Builder_Where::and_where_open
	 * @covers Database_Query_Builder_Where::and_where_close
	 * @covers Database_Query_Builder_Where::where_close_empty
	 * @covers Database_Query_Builder_Where::order_by
	 * @covers Database_Query_Builder_Where::limit
	 * @covers Database_Query_Builder_Select::from
	 * @covers Database_Query_Builder_Select::offset
	 * @covers Database_Query_Builder_Select::compile
	 * @covers Database_Query_Builder_Insert::values
	 * @covers Database_Query_Builder_Insert::compile
	 * @covers Database_Query_Builder_Update::set
	 * @covers Database_Query_Builder_Update::compile
	 * @covers Database_Query_Builder_Delete::compile
	 * @covers Database::quote_column
	 * @covers Database::quote_table
	 * @covers Database::quote
	 * @covers Database_PDO::escape
	 * @dataProvider provider_compile
	 */
	public function test_compile($expected, Closure $build)
	{
		$this->assertSame($expected, $build()->compile($this->db));
	}

	/**
	 * @test
	 * @covers Database::quote_table
	 * @covers Database::quote_column
	 * @covers Database::table_prefix
	 * @covers Database_Query_Builder::_compile_join
	 * @covers Database_Query_Builder_Join::on
	 * @covers Database_Query_Builder_Join::compile
	 */
	public function test_compile_adds_table_prefix()
	{
		$db = Database::instance('unittest_sqlite_prefix', [
			'type'         => 'PDO',
			'connection'   => [
				'dsn'        => 'sqlite::memory:',
				'persistent' => FALSE,
			],
			'table_prefix' => 'kn_',
			'identifier'   => '"',
			'caching'      => FALSE,
		]);

		try
		{
			$query = DB::select('p.name')
				->from(['people', 'p'])
				->join('pets')
				->on('pets.person_id', '=', 'p.id')
				->where('p.age', '>', 25);

			$this->assertSame(
				'SELECT "kn_p"."name" FROM "kn_people" AS "kn_p" JOIN "kn_pets" ON ("kn_pets"."person_id" = "kn_p"."id") WHERE "kn_p"."age" > 25',
				$query->compile($db)
			);
		}
		finally
		{
			$db->disconnect();
		}
	}

	/**
	 * @test
	 * @covers Database_Query::execute
	 * @covers Database_PDO::connect
	 * @covers Database_PDO::query
	 */
	public function test_select_returns_matching_rows()
	{
		$result = DB::select('name', 'age')
			->from('people')
			->where('age', '>=', 30)
			->order_by('age')
			->execute($this->db);

		$this->assertInstanceOf('Database_Result', $result);
		$this->assertSame([
			['name' => 'alice', 'age' => 30],
			['name' => 'carol', 'age' => 35],
		], $result->as_array());
	}

	/**
	 * @test
	 * @covers Database_Query::execute
	 * @covers Database_PDO::query
	 */
	public function test_insert_returns_insert_id_and_affected_rows()
	{
		$this->assertSame(['4', 1], DB::insert('people', ['name'])->values(['dave'])->execute($this->db));

		list(, $affected_rows) = DB::insert('people', ['name'])->values(['erin'])->values(['frank'])->execute($this->db);

		$this->assertSame(2, $affected_rows);
	}

	/**
	 * @test
	 * @covers Database_Query::execute
	 * @covers Database_PDO::query
	 */
	public function test_update_returns_affected_rows()
	{
		$this->assertSame(2, DB::update('people')->set(['age' => 50])->where('age', '<', 35)->execute($this->db));
		$this->assertSame(['alice' => 50, 'bob' => 50, 'carol' => 35], DB::select('name', 'age')->from('people')->execute($this->db)->as_array('name', 'age'));
	}

	/**
	 * @test
	 * @covers Database_Query::execute
	 * @covers Database_PDO::query
	 * @covers Database::count_records
	 */
	public function test_delete_returns_affected_rows()
	{
		$this->assertSame(1, DB::delete('people')->where('name', '=', 'bob')->execute($this->db));
		$this->assertSame(0, DB::delete('people')->where('name', '=', 'bob')->execute($this->db));
		$this->assertSame(2, $this->db->count_records('people'));
	}

	/**
	 * Provides test data for test_quote()
	 *
	 * @return array
	 */
	public function provider_quote()
	{
		return [
			["'it''s'", "it's"],
			['NULL', NULL],
			["'1'", TRUE],
			["'0'", FALSE],
			[42, 42],
			['1.500000', 1.5],
			['COUNT("id")', DB::expr('COUNT("id")')],
			["('a', 1, NULL)", ['a', 1, NULL]],
		];
	}

	/**
	 * @test
	 * @covers Database::quote
	 * @covers Database_PDO::escape
	 * @covers Database_Expression::compile
	 * @dataProvider provider_quote
	 */
	public function test_quote($expected, $value)
	{
		$this->assertSame($expected, $this->db->quote($value));
	}

	/**
	 * @test
	 * @covers Database_Query::as_object
	 * @covers Database_PDO::query
	 * @covers Database_Result_Cached::current
	 */
	public function test_as_object_returns_stdclass()
	{
		$row = DB::select('name')->from('people')->where('id', '=', 2)->as_object()->execute($this->db)->current();

		$this->assertInstanceOf('stdClass', $row);
		$this->assertSame('bob', $row->name);
	}

	/**
	 * @test
	 * @covers Database_Query::as_object
	 * @covers Database_PDO::query
	 * @covers Database_Result::as_array
	 * @covers Database_Result::get
	 */
	public function test_as_object_returns_instances_of_class()
	{
		$result = DB::select('id', 'name')
			->from('people')
			->order_by('id')
			->as_object('Kohana_Database_PDOTest_Person')
			->execute($this->db);

		$this->assertContainsOnlyInstancesOf('Kohana_Database_PDOTest_Person', $result->as_array());
		$this->assertSame('alice', $result->get('name'));
		$this->assertSame([1 => 'alice', 2 => 'bob', 3 => 'carol'], $result->as_array('id', 'name'));
	}

	/**
	 * @test
	 * @covers Database_PDO::begin
	 * @covers Database_PDO::rollback
	 */
	public function test_rollback_discards_changes()
	{
		$this->assertTrue($this->db->begin());

		DB::delete('people')->execute($this->db);

		$this->assertTrue($this->db->rollback());
		$this->assertSame(3, $this->db->count_records('people'));
	}

	/**
	 * The connection sees its own uncommitted writes, so a new transaction
	 * is started afterwards: PDO refuses to begin one while another is open.
	 *
	 * @test
	 * @covers Database_PDO::begin
	 * @covers Database_PDO::commit
	 * @covers Database_PDO::rollback
	 */
	public function test_commit_keeps_changes()
	{
		$this->assertTrue($this->db->begin());

		DB::insert('people', ['name'])->values(['dave'])->execute($this->db);

		$this->assertTrue($this->db->commit());
		$this->assertTrue($this->db->begin());
		$this->assertTrue($this->db->rollback());
		$this->assertSame(4, $this->db->count_records('people'));
	}

	/**
	 * @test
	 * @covers Database_PDO::create_function
	 * @covers Database_PDO::query
	 */
	public function test_create_function_is_callable_from_sql()
	{
		$this->db->create_function('double_value', function ($value) {
			return $value * 2;
		}, 1);

		$result = $this->db->query(Database::SELECT, 'SELECT double_value("age") AS "doubled" FROM "people" WHERE "id" = 1');

		$this->assertSame(60, $result->get('doubled'));
	}

	/**
	 * @test
	 * @covers Database_PDO::create_aggregate
	 * @covers Database_PDO::query
	 */
	public function test_create_aggregate_is_callable_from_sql()
	{
		$this->db->create_aggregate('total_age', function ($total, $row, $value) {
			return $total + $value;
		}, function ($total, $rows) {
			return $total;
		}, 1);

		$result = $this->db->query(Database::SELECT, 'SELECT total_age("age") AS "total" FROM "people"');

		$this->assertSame(90, $result->get('total'));
	}

	/**
	 * @test
	 * @covers Database_PDO::query
	 */
	public function test_invalid_sql_throws_database_exception()
	{
		$this->expectException(Database_Exception::class);
		$this->expectExceptionMessage('[ SELECT * FROM "missing" ]');

		DB::select()->from('missing')->execute($this->db);
	}

	/**
	 * The sort direction is checked when the query is compiled, not when
	 * order_by() is called.
	 *
	 * @test
	 * @covers Database_Query_Builder_Where::order_by
	 * @covers Database_Query_Builder::_compile_order_by
	 */
	public function test_invalid_sort_direction_throws_database_exception()
	{
		$query = DB::select()->from('people')->order_by('name', 'bogus');

		$this->expectException(Database_Exception::class);
		$this->expectExceptionMessage('Invalid sorting direction');

		$query->compile($this->db);
	}

	/**
	 * @test
	 * @covers Database_PDO::connect
	 */
	public function test_connect_with_invalid_dsn_throws_database_exception()
	{
		$db = Database::instance('unittest_invalid_dsn', [
			'type'       => 'PDO',
			'connection' => [
				'dsn'        => 'nodriver:x',
				'persistent' => FALSE,
			],
		]);

		$this->expectException(Database_Exception::class);

		try
		{
			$db->connect();
		}
		finally
		{
			$db->disconnect();
		}
	}

	/**
	 * @test
	 * @covers Database_Result::as_array
	 */
	public function test_result_as_array_with_key_and_value_columns()
	{
		$result = DB::select('id', 'name')->from('people')->order_by('id')->execute($this->db);

		$this->assertSame(['alice', 'bob', 'carol'], $result->as_array(NULL, 'name'));
		$this->assertSame(['bob' => ['id' => 2, 'name' => 'bob']], array_slice($result->as_array('name'), 1, 1));
		$this->assertSame(['alice' => 1, 'bob' => 2, 'carol' => 3], $result->as_array('name', 'id'));
	}

	/**
	 * @test
	 * @covers Database_Result::get
	 * @covers Database_Result::count
	 */
	public function test_result_get_and_count()
	{
		$result = DB::select('name', 'age')->from('people')->order_by('age')->execute($this->db);

		$this->assertCount(3, $result);
		$this->assertSame('bob', $result->get('name'));
		$this->assertSame('default', $result->get('missing', 'default'));

		$empty = DB::select()->from('people')->where('age', '>', 100)->execute($this->db);

		$this->assertCount(0, $empty);
		$this->assertNull($empty->get('name'));
	}
}

class Kohana_Database_PDOTest_Person
{
	public $id;
	public $name;
}

<?php



class DocumentationTest extends TestCase
{

    /**
     * exists documentation.
     * @test
     * @return void
     */
    public function testDocumentation()
    {
        $response = $this->call('GET', '/api/documentation');
        $this->assertEquals(200, $response->status());
    }
    
}

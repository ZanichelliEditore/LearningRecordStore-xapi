<?php


use App\Models\Lrs;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\WithoutMiddleware;

class LrsTest extends TestCase
{
    use WithoutMiddleware;

     /**
     * @test
     * @return void
     */
    public function lrsCreateSuccessTest()
    {
        $lrs = factory(Lrs::class)->make();

        $response = $this->json('POST', 'lrs', $lrs->toArray());
        $response->assertResponseStatus(201);
        $response->seeJsonStructure([
            'message',
            'data'
        ]);
    }

     /**
     * @test
     * @return void
     */
    public function lrsCreateFailValidationTest()
    {
        $lrs = factory(Lrs::class)->make([
            'title' => '',
            'folder' => '',
            'description' => Str::random(201)
        ]);

        $response = $this->json('POST', 'lrs', $lrs->toArray());
        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'message',
            'errors' => [
                'title',
                'folder',
                'description'
            ]
        ]);

    }

     /**
     * @test
     * @return void
     */
    public function lrsGetAllSuccessTest()
    {
        $lrs = factory(Lrs::class)->create();

        $response = $this->json('GET', 'lrs');
        $response->assertResponseStatus(200);
        $response->seeJsonStructure(['data']);

    }

    /**
     * @test
     * @return void
    */
    public function lrsGetStatementsEmptyTest()
    {
        $lrs = factory(Lrs::class)->create();
        $response = $this->json('GET', 'lrs/' . $lrs->_id . '/statements');
        $response->assertResponseStatus(204);
    }


     /**
     * @test
     * @return void
     */
    public function lrsDeleteSuccessTest()
    {
        $lrs = factory(Lrs::class)->create();

        $response = $this->json('DELETE', 'lrs/' . (string) $lrs->_id);

        $response->assertResponseStatus(200);

    }

    /**
     * @test
     * @return void
     */
    public function lrsDeleteNotFoundTest()
    {

        $response2 = $this->json('DELETE', 'lrs/test');

        $response2->assertResponseStatus(404);

    }

}
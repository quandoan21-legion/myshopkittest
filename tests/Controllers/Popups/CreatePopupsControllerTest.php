<?php

namespace MyShopKitTest\Controllers\Popups;

use MyShopKitTest\CommonController;

class CreatePopupsControllerTest extends CommonController
{
    /**
     * @depends testCreatePopups
     */
    public function testGetPopups($postID)
    {
        $aResponse = $this->setUserLogin('admin')->restApi('popups/' . $postID, 'GET', [
                'pluck' => 'title,id'
            ]
        );
        $this->assertEquals($postID, $aResponse['data']['id']);
        $this->assertCount(2, $aResponse['data']);

        return $aResponse['data']['id'];
    }

    /**
     * @depends testGetPopups
     */
    public function testUpdatePopups($postID)
    {
        $aResponse = $this->setUserLogin('admin')->restApi('popups/' . $postID, 'PUT',
            [
                'title'  => 'tests1',
                'config' => 'tests'
            ]
        );
        $this->assertEquals($postID, $aResponse['data']['id']);

        return $aResponse['data']['id'];
    }

    /**
     * @depends testUpdatePopups
     */
    public function testGetPopupsWithOneParam($postID)
    {
        /**
         * Param default is config
         */
        $aResponse = $this->setUserLogin('admin')->restApi('popups/' . $postID . '/config', 'GET');
        $this->assertCount(2, $aResponse);
        $this->assertIsString($aResponse['data']['config']);
        return $postID;
    }

    /**
     * @depends testGetPopupsWithOneParam
     */
    public function testDeletePopup(): array
    {
        $aPostID = ($this->createPopups());
        $aResponse = $this->setUserLogin('admin')->restApi('popups/' . $aPostID[0], 'DELETE',);
        $this->assertEquals($aPostID[0], $aResponse['data']['id']);
        return $aPostID;
    }

    public function createPopups(): array
    {
        $aPostID = [];
        for ($i = 1; $i < 4; $i++) {
            $aPostID[] = $this->testCreatePopups();
        }
        return $aPostID;
    }

    /**
     * test Tạo Post Meta với user mặc định là admin
     */
    public function testCreatePopups()
    {
        $aResponse = $this->setUserLogin('admin')->restApi('popups', 'POST',
            [
                'title'  => 'tests1',
                'config' => 'tests1'
            ]
        );
        $this->assertCount(2, $aResponse);
        $this->assertIsString($aResponse['data']['id']);

        return $aResponse['data']['id'];
    }

    /**
     * @depends testDeletePopup
     */
    public function testDeletePopups($aPostID): array
    {
        $aResponse = $this->setUserLogin('admin')->restApi('popups', 'DELETE', [
            'ids' => (string)$aPostID[1] . ',' . (string)$aPostID[2]
        ]);
        $this->assertEquals(true, in_array($aPostID[1], explode(',', $aResponse['data']['id'])));
        return $aPostID;
    }
}

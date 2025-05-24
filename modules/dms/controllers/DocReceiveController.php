<?php

namespace app\modules\dms\controllers;
use Yii;
use yii\web\Response;

class DocReceiveController extends \yii\web\Controller
{
    // READ
    public function actionIndex()
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $documentTemps =  $this->readData();
        return $this->render('index',['documentTemps' => $documentTemps]);
    }


    private function getFilePath()
    {
        return Yii::getAlias('@app/doc_receive/data.json');
    }

    private function readData()
    {
        $file = $this->getFilePath();
        if (!file_exists($file)) return [];

        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }

    private function writeData($data)
    {
        $file = $this->getFilePath();
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }


    // CREATE
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $data = $this->readData();

        $newItem = [
            'id' => count($data) > 0 ? max(array_column($data, 'id')) + 1 : 1,
            'name' => $request->post('name'),
            'price' => (float) $request->post('price'),
        ];

        $data[] = $newItem;
        $this->writeData($data);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => true, 'item' => $newItem];
    }

    // UPDATE
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $data = $this->readData();

        foreach ($data as &$item) {
            if ($item['id'] == $id) {
                $item['name'] = $request->post('name', $item['name']);
                $item['price'] = (float) $request->post('price', $item['price']);
                $this->writeData($data);

                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true, 'item' => $item];
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => false, 'error' => 'Item not found'];
    }

    // DELETE
    public function actionDelete($id)
    {
        $data = $this->readData();
        $newData = array_filter($data, fn($item) => $item['id'] != $id);

        if (count($newData) == count($data)) {
            return ['success' => false, 'error' => 'Item not found'];
        }

        $this->writeData(array_values($newData)); // reset index

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => true];
    }

}

public function actionLeavelt1($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Leave::findOne($id);
        // $this->CreateDir($model->id);
        $title = 'LT1-ใบลากิจ';
        $result_name = $title . '-' . $model->id . '.docx';
        // $result_name = $model->id . '.docx';
        $word_name = 'LT1-ใบลากิจ.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', $model->leaveType->title);
        $filePath = Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        
        if (file_exists($filePath)) {
            return $this->Show($result_name);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }


    private function Show($filename)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/leave/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('show', ['filename' => $filename]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . $filename), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . $filename, true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }
    
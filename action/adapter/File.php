<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 11:26
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class GridView
 * @package execut\actions\action
 * @property Model $filter
 */
class File extends Adapter
{
    public $modelClass = null;
    public $model = null;
    public $dataAttribute = 'data';
    public $nameAttribute = 'name';
    public $mimeTypeAttribute = 'mime_type';
    public $mimeType = null;
    public $extensionAttribute = 'extension';
    public $extensionIsRequired = true;
    protected function _run() {
        $attributes = $this->actionParams->get;
        if (empty($attributes['id'])) {
            throw new NotFoundHttpException('Id attribute required');
        }

        $class = $this->modelClass;
        unset($attributes['r']);

        if ($this->extensionIsRequired && empty($attributes['extension'])) {
            throw new NotFoundHttpException('Extension required');
        }

        if (!empty($attributes['dataAttribute'])) {
            $dataAttribute = $attributes['dataAttribute'];
            unset($attributes['dataAttribute']);
        } else {
            $dataAttribute = $this->dataAttribute;
        }

        $extensionAttribute = $this->extensionAttribute;
        if (!$class::getTableSchema()->getColumn($dataAttribute)) {
            throw new NotFoundHttpException();
        }

        $selectedAttributes = [
            $this->nameAttribute,
            $dataAttribute,
            $this->mimeTypeAttribute,
        ];

        if ($this->extensionIsRequired) {
            $selectedAttributes[] = $this->extensionAttribute;
        }

        $selectedAttributes = array_filter($selectedAttributes);
        $findAttributes = [
            'id' => (int) abs($attributes['id']),
        ];

        if ($this->extensionIsRequired) {
            $findAttributes[$extensionAttribute] = $attributes['extension'];
        }

        $result = $class::find()->select($selectedAttributes)->andWhere($findAttributes)->one();
        if (!$result) {
            throw new NotFoundHttpException('File by url "' . \yii::$app->request->getUrl() . '" not found');
        }

        if ($this->extensionIsRequired && strtolower($result->$extensionAttribute) !== $findAttributes[$extensionAttribute]) {
            throw new NotFoundHttpException('File extension is wrong');
        }

        $this->model = $result;

        $response = \Yii::$app->getResponse();
        if ($this->mimeTypeAttribute || $this->mimeType) {
            if ($this->mimeType) {
                if (is_callable($this->mimeType)) {
                    $mimeType = call_user_func_array($this->mimeType, [$result, $dataAttribute]);
                } else {
                    $mimeType = $this->mimeType;
                }
            }

            if (empty($mimeType) && $this->mimeTypeAttribute) {
                $mimeTypeAttribute = $this->mimeTypeAttribute;
                $mimeType = $result->$mimeTypeAttribute;
            }

            if (strpos($mimeType, 'image/') === 0) {
                $response->headers->set('Content-Type', $mimeType);
            } else {
                $response->setDownloadHeaders($result->{$this->nameAttribute}, $mimeType);
            }
        } else {
            $response->headers->set('Content-Type', 'image/jpeg');
        }

//        var_dump(stream_get_contents($result->{$dataAttribute}));
//        exit;
        $response = $this->getResponse([
            'format' => Response::FORMAT_RAW,
            'content' => stream_get_contents($result->{$dataAttribute}),
        ]);

        return $response;
    }
}
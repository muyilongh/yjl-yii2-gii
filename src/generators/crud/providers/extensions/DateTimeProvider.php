<?php

namespace Yjl\Gii\generators\crud\providers\extensions;

class DateTimeProvider extends \Yjl\Gii\base\Provider
{
    public function activeField($attribute)
    {
        switch (true) {
            case in_array($attribute, $this->columnNames):
                $this->generator->requires[] = 'zhuravljov/yii2-datetime-widgets';

                return <<<EOS
\$form->field(\$model, '{$attribute}')->widget(\zhuravljov\widgets\DateTimePicker::className(), [
    'options' => ['class' => 'form-control'],
    'clientOptions' => [
        'autoclose' => true,
        'todayHighlight' => true,
        'format' => 'yyyy-mm-dd hh:ii',
    ],
])
EOS;
                break;
            default:
                return null;
        }
    }
}

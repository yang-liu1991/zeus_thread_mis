<div> 
    <?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'action'=>array('workOrder/list'),
            'method'=>'get',
            'type'=>'search',
			'htmlOptions'=>array('class'=>'well'),
        ));
    ?>
        <?php
				echo CHtml::hiddenField("offerStatus", $model->offerStatus);
        ?>

        <div class="input-prepend">
            <span class="add-on"><i class="icon-search"></i></span>
            <?php echo CHtml::textField("search", $model->search, array("placeholder"=>"请输入关键字")); ?>
        </div>

        <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=>'搜索')); ?>

    <?php $this->endWidget(); ?>
</div>

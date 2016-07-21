<?php
class Magemaven_OrderComment_Model_System_Config_Source_Inputs
{
	public function toOptionArray(){
		return array(
			array(
				'value' => 'textarea',
				'label' => 'Text area (multiline)',
			),
			array(
				'value' => 'input',
				'label' => 'Input (single line)',
			),
		);
	}
}

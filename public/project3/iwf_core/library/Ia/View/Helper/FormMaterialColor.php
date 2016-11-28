<?php
require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormMaterialColor extends Zend_View_Helper_FormText
{

	public function formMaterialColor($name, $value = null, $attribs = null,
			$options = null, $listsep = "<br />\n")
	{	
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        $this->view->headLink()->appendStylesheet('/vendor/spectrum/spectrum.css');
        $this->view->headScript()->appendFile('/vendor/spectrum/spectrum.js');
        $this->view->headScript()->captureStart();
        if(isset($attribs['primary']) && $attribs['primary']==true){
            $palette = \Ia_Form_Element_MaterialColor::$primary_palette;
        } elseif(isset($attribs['custom'])) {
            $palette = $attribs['custom'];
        } else {
            $palette = \Ia_Form_Element_MaterialColor::$palette;
        }
        ?>
            $("#<?=$this->view->escape($id);?>").spectrum({
                showPaletteOnly: true,
                showPalette: true,
                preferredFormat: "hex",
                change: function(color) {
                    color.toHexString(); // #ff0000
                },
                color: '<?=($value===null) ? null : $value;?>',
                palette: [
                    ['<?=implode('\',\'',$palette);?>']
                ]
            });     
        <?php
        $this->view->headScript()->captureEnd();
		$xhtml = '<div>'.parent::formText($name,$value,$attribs,$options,$listsep).'</div>';
		return $xhtml;
	}

}
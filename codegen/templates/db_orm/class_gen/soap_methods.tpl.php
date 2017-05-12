
    ////////////////////////////////////////
    // METHODS for SOAP-BASED WEB SERVICES
    ////////////////////////////////////////

    public static function getSoapComplexTypeXml()
    {
        $strToReturn = '<complexType name="<?= $objTable->ClassName ?>"><sequence>';
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php if (!$objColumn->Reference || $objColumn->Reference->IsType) { ?>
        $strToReturn .= '<element name="<?= $objColumn->PropertyName ?>" type="xsd:<?= \QCubed\Type::SoapType($objColumn->VariableType) ?>"/>';
<?php } ?><?php if ($objColumn->Reference && (!$objColumn->Reference->IsType)) { ?>
        $strToReturn .= '<element name="<?= $objColumn->Reference->PropertyName ?>" type="xsd1:<?= $objColumn->Reference->VariableType ?>"/>';
<?php } ?>
<?php } ?>
        $strToReturn .= '<element name="__blnRestored" type="xsd:boolean"/>';
        $strToReturn .= '</sequence></complexType>';
        return $strToReturn;
    }

    public static function alterSoapComplexTypeArray(&$strComplexTypeArray)
    {
        if (!array_key_exists('<?= $objTable->ClassName ?>', $strComplexTypeArray)) {
            $strComplexTypeArray['<?= $objTable->ClassName ?>'] = <?= $objTable->ClassName ?>::GetSoapComplexTypeXml();
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->Reference && (!$objColumn->Reference->IsType)) { ?>
            <?= $objColumn->Reference->VariableType ?>::AlterSoapComplexTypeArray($strComplexTypeArray);
<?php } ?>
<?php } ?>
        }
    }

    public static function getArrayFromSoapArray($objSoapArray)
    {
        $objArrayToReturn = array();

        foreach ($objSoapArray as $objSoapObject)
            array_push($objArrayToReturn, <?= $objTable->ClassName ?>::GetObjectFromSoapObject($objSoapObject));

        return $objArrayToReturn;
    }

    public static function getObjectFromSoapObject($objSoapObject)
    {
        $objToReturn = new <?= $objTable->ClassName ?>();
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php if (!$objColumn->Reference || $objColumn->Reference->IsType) { ?>
        if (property_exists($objSoapObject, '<?= $objColumn->PropertyName ?>'))
<?php if ($objColumn->VariableType != \QCubed\Type::DATE_TIME) { ?>
            $objToReturn-><?= $objColumn->VariableName ?> = $objSoapObject-><?= $objColumn->PropertyName ?>;
<?php } ?><?php if ($objColumn->VariableType == \QCubed\Type::DATE_TIME) { ?>
            $objToReturn-><?= $objColumn->VariableName ?> = new QDateTime($objSoapObject-><?= $objColumn->PropertyName ?>);
<?php } ?>
<?php } ?><?php if ($objColumn->Reference && (!$objColumn->Reference->IsType)) { ?>
        if ((property_exists($objSoapObject, '<?= $objColumn->Reference->PropertyName ?>')) &&
            ($objSoapObject-><?= $objColumn->Reference->PropertyName ?>))
            $objToReturn-><?= $objColumn->Reference->PropertyName ?> = <?= $objColumn->Reference->VariableType ?>::GetObjectFromSoapObject($objSoapObject-><?= $objColumn->Reference->PropertyName ?>);
<?php } ?>
<?php } ?>
        if (property_exists($objSoapObject, '__blnRestored'))
            $objToReturn->__blnRestored = $objSoapObject->__blnRestored;
        return $objToReturn;
    }

    public static function getSoapArrayFromArray($objArray)
    {
        if (!$objArray)
            return null;

        $objArrayToReturn = array();

        foreach ($objArray as $objObject)
            array_push($objArrayToReturn, <?= $objTable->ClassName ?>::GetSoapObjectFromObject($objObject, true));

        return unserialize(serialize($objArrayToReturn));
    }

    public static function getSoapObjectFromObject($objObject, $blnBindRelatedObjects)
    {
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->VariableType == \QCubed\Type::DATE_TIME) { ?>
        if ($objObject-><?= $objColumn->VariableName ?>)
            $objObject-><?= $objColumn->VariableName ?> = $objObject-><?= $objColumn->VariableName ?>->qFormat(QDateTime::FormatSoap);
<?php } ?><?php if ($objColumn->Reference && (!$objColumn->Reference->IsType)) { ?>
        if ($objObject-><?= $objColumn->Reference->VariableName ?>)
            $objObject-><?= $objColumn->Reference->VariableName ?> = <?= $objColumn->Reference->VariableType ?>::GetSoapObjectFromObject($objObject-><?= $objColumn->Reference->VariableName ?>, false);
        else if (!$blnBindRelatedObjects)
            $objObject-><?= $objColumn->VariableName ?> = null;
<?php } ?>
<?php } ?>
        return $objObject;
    }

<?php
/* @var array $scriptProperties */
if (isset($parents) && $parents === '') {
	$scriptProperties['parents'] = $modx->resource->id;
}
if (!empty($returnIds)) {
	$scriptProperties['return'] = 'ids';
}

// Adding extra parameters into special place so we can put them in results
/** @var modSnippet $snippet */
$additionalPlaceholders = array();
if ($snippet = $modx->getObject('modSnippet', array('name' => 'pdoResources'))) {
	$properties = unserialize($snippet->properties);
	foreach ($scriptProperties as $k => $v) {
		if (!isset($properties[$k])) {
			$additionalPlaceholders[$k] = $v;
		}
	}
}
$scriptProperties['additionalPlaceholders'] = $additionalPlaceholders;

/* @var pdoFetch $pdoFetch */
$fqn_ar = explode(':',$modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true));
$fqn = isset($fqn_ar[0]) ? $fqn_ar[0] : 'pdotools.pdofetch';
$classPath = isset($fqn_ar[1]) ? str_replace('{core_path}',$modx->getOption('core_path'),$fqn_ar[1]) : '';  
if (!$pdoClass = $modx->loadClass($fqn, $classPath, false, true)) {return false;}
$pdoFetch = new $pdoClass($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');
$output = $pdoFetch->run();

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$log .= '<pre class="pdoResourcesLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($returnIds)) {
	$modx->setPlaceholder('pdoResources.log', $log);
	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
	}
	else {
		return $output;
	}
}
elseif (!empty($toSeparatePlaceholders)) {
	$output['log'] = $log;
	$modx->setPlaceholders($output, $toSeparatePlaceholders);
}
else {
	$output .= $log;

	if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
		$output = $pdoFetch->getChunk($tplWrapper, array('output' => $output), $pdoFetch->config['fastMode']);
	}

	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
	}
	else {
		return $output;
	}
}
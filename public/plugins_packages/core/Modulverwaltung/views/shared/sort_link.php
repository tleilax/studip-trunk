<th<?
if ($controller->sortby == $field ) :
    echo ' class="sort' . mb_strtolower($controller->order) . '"';
endif;
foreach ($attributes as $key => $value) :
    if (in_array($key, array('style', 'colspan'))) :
        echo ' ' . $key . '="' . $value . '"';
    endif;
endforeach;
?>>
<?
if ($controller->sortby == $field) :
    if ($controller->order != 'DESC') :
        $params = array('sortby' . $controller->page_params_suffix => $field, 'order' . $controller->page_params_suffix => 'DESC');
    else :
        $params = array('sortby' . $controller->page_params_suffix => $field, 'order' . $controller->page_params_suffix => 'ASC');
    endif;
else :
    $params = array('sortby' . $controller->page_params_suffix => $field, 'order' . $controller->page_params_suffix => 'ASC');
endif;
?>
    <a href="<?= URLHelper::getURL($controller->url_for($action), $params) ?>"><?= htmlReady($text) ?></a>
</th>
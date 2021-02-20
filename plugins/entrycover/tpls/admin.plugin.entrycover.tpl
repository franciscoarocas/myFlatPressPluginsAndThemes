<h2>{$plang.head}</h2>

{include file=shared:errorlist.tpl}
{html_form class=option-set}
    <dl>
        <label><input type="checkbox" id="entryCoverTagCheckBox" name="entryCoverTagCheckBox" {if isset($allowTag) && $allowTag}checked="checked"{/if}>{$plang.useCoverTag}</label><br>
        <p>{$plang.useCoverTagNote}</p>
    </dl>
    <div class="button-bar">
        <button type="submit">{$plang.saveButton}</button>
    </div>
{/html_form}
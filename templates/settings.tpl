<script>
$(function() {ldelim}
    $('#webchatNagastraSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
{rdelim});
</script>

<form
    class="pkp_form"
    id="webchatNagastraSettingsForm"
    method="post"
    action="{url router=$smarty.const.ROUTE_COMPONENT op='manage' category='generic' plugin=$pluginName verb='settings' save=true}"
>
    {csrf}

    {fbvFormArea id="webchatNagastraSettingsArea"}
        {fbvFormSection list=true}
            {fbvElement
                type="checkbox"
                id="webchatEnabled"
                name="webchatEnabled"
                checked=$webchatEnabled
                label="plugins.generic.webchatNagastra.form.enabled"
            }
        {/fbvFormSection}

        {fbvFormSection title="plugins.generic.webchatNagastra.form.scriptUrl"}
            {fbvElement
                type="text"
                id="scriptUrl"
                name="scriptUrl"
                value=$scriptUrl
                placeholder="https://chat.nagastra.org/widget.js"
                size=$smarty.const.FBV_ELEMENT_SIZE_LARGE
            }
            <p class="pkp_help">{translate key="plugins.generic.webchatNagastra.form.scriptUrlHelp"}</p>
        {/fbvFormSection}

        {fbvFormSection title="plugins.generic.webchatNagastra.form.position" list=true}
            <select name="position" id="position" class="selectMenu">
                <option value="frontend"{if $position == 'frontend'} selected="selected"{/if}>{translate key="plugins.generic.webchatNagastra.form.position.frontend"}</option>
                <option value="all"{if $position == 'all'} selected="selected"{/if}>{translate key="plugins.generic.webchatNagastra.form.position.all"}</option>
            </select>
        {/fbvFormSection}
    {/fbvFormArea}

    {fbvFormButtons submitText="common.save"}
</form>

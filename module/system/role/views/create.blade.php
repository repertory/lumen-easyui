<div class="easyui-panel" fit="false" border="false" iconCls="fa fa-edit">

    <form method="post">
        <dl>
            <dd>
                <input class="easyui-numberbox" name="parent" label="上级" labelWidth="48" required="true" data-options="{validType: {min: 0}}"
                    style="width:100%">
            </dd>
            <dd>
                <input class="easyui-textbox" name="role" label="标识" labelWidth="48" required="true" data-options="{validType: {length: [2, 10]}}"
                    style="width:100%">
            </dd>
            <dd>
                <input class="easyui-textbox" name="name" label="角色" labelWidth="48" required="true" data-options="{validType: {length: [2, 24]}}"
                    style="width:100%">
            </dd>
        </dl>
    </form>

</div>

<style type="text/css">
    :host dl {
        margin: 24px 0;
    }

    :host dl dd {
        margin: 16px;
        max-width: 360px;
    }
</style>

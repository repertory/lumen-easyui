<div class="easyui-panel" fit="false" border="false" iconCls="fa fa-edit">

    <form method="post">
        <dl>
            <dd>
                <input class="easyui-textbox" name="name" label="姓名" labelWidth="48" required="true" data-options="{validType: {length: [2, 24]}}"
                    style="width:100%">
            </dd>
            <dd>
                <input class="easyui-textbox" name="email" type="email" label="邮箱" labelWidth="48" required="true" validateOnCreate="false"
                    data-options="{validType: {email: true, remote: ['{{ module_url('wangdong/easyui/exist', ['type' => 'email','reverse' => true]) }}', 'email']}}"
                    style="width:100%">
            </dd>
            <dd>
                <input class="easyui-passwordbox" name="password" label="密码" labelWidth="48" required="true" data-options="{validType: {length: [6, 32]}}"
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

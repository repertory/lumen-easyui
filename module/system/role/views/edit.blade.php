<div class="easyui-panel" fit="false" border="false" iconCls="fa fa-edit">

    <form method="post">
        <dl>
            <dd>
                <input class="easyui-combotree" name="parent" label="上级" labelWidth="48" required="true" url="{{ module_url('system/role/combotree') }}"
                    data-options="{formatter: function(node){return node.name;},textField:'name'}" style="width:100%">
            </dd>
            <dd>
                <input class="easyui-textbox" name="role" label="标识" labelWidth="48" required="true" data-options="{validType: {length: [2, 10], remote: ['{{ module_url('system/role/exist', ['type' => 'role', 'reverse' => true, 'except' => $data->role]) }}', 'role']}}"
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
    :module dl {
        margin: 24px 0;
    }

    :module dl dd {
        margin: 16px;
        max-width: 360px;
    }
</style>

<script type="text/javascript">
    $(':module').options({
        form: $('form', ':module'),
        data: {!! $data->toJson() !!},
        // 初始化
        init: function () {
            this.form.form('load', this.data);
        }
    }).init();

</script>

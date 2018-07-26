<div class="easyui-panel" fit="false" border="false" iconCls="fa fa-edit">
    <form method="post">
        <dl>
            <dd>
                <input class="easyui-combotree" name="roles[]" label="角色" labelWidth="48" required="true" url="{{ module_url('system/user/role_combotree') }}"
                    multiple="true" cascadeCheck="false" data-options="{formatter: function(node){return node.name;},textField:'name'}"
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
            var self = this;
            this.form.form('load', {
                'roles[]': self.data.map(function(role){
                    return role.id;
                })
            });
        }
    }).init();

</script>

<div class="easyui-panel" fit="false" border="false" iconCls="fa fa-edit">
    <form method="post">
        <dl>
            <dd>
                <input class="easyui-textbox" name="name" label="姓名" labelWidth="48" required="true" data-options="{validType: {length: [2, 24]}}"
                    style="width:100%">
            </dd>
            <dd>
                <input class="easyui-textbox" name="email" type="email" label="邮箱" labelWidth="48" required="true" validateOnCreate="false"
                    data-options="{validType: {email: true, remote: ['{{ module_url('wangdong/easyui/exist', ['type' => 'email','reverse' => true, 'except' => $data->email]) }}', 'email']}}"
                    style="width:100%">
            </dd>
        </dl>
    </form>
    <div class="buttons">
        <a class="easyui-linkbutton" iconCls="fa fa-save" method="save">保存</a>
        <a class="easyui-linkbutton" iconCls="fa fa-remove" method="cancle">取消</a>
    </div>
</div>

<style type="text/css">
    :host dl {
        margin: 24px 0;
    }

    :host dl dd {
        margin: 16px;
        max-width: 360px;
    }

    :host .buttons {
        margin: 8px 0;
    }
</style>

<script type="text/javascript">
    $(':host').options({
        parent: $(':host').parents('[module]').options(),
        form: $('form', ':host'),
        data: {!! $data->toJson() !!},
        // 初始化
        init: function () {
            this.form.form('load', this.data);
            this.event();
        },
        // 事件监听
        event: function () {
            var self = this;
            $('[method]', ':host').on('click', function () {
                var method = $(this).attr('method');
                typeof self[method] === 'function' && self[method].call(self, this);
            });
        },
        // 保存
        save: function(target) {
            var self = this;

            // 获取当前内容所在的行数
            var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
            var index = parseInt(tr.attr('datagrid-row-index'));

            this.form.form('ajax', {
                progressbar: '数据发送中...',
                url: '{{ module_url('system/user/edit', ['id' => $data->id]) }}',
                onSubmit: function () {
                    return $(this).form('validate');
                },
                success: function (data) {
                    $.messager.success('操作提示', '修改成功');
                    // 收起当前行
                    self.parent.datagrid.datagrid('collapseRow', index);
                    // 更新当前行的数据
                    self.parent.datagrid.datagrid('updateRow', {
                        index: index,
                        row: data
                    });
                },
                error: '操作提示'
            });
        },
        // 取消
        cancle: function(target) {
            // 获取当前内容所在的行数
            var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
            var index = parseInt(tr.attr('datagrid-row-index'));
            // 收起当前行
            this.parent.datagrid.datagrid('collapseRow', index);
        }
    }).init();

</script>

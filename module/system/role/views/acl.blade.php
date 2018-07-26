<div class="easyui-panel" fit="false" border="false" iconCls="fa fa-universal-access">

    <div class="toolbar">
        <a class="easyui-linkbutton" iconCls="fa fa-folder-o" plain="true" method="collapse">收起</a>
        <a class="easyui-linkbutton" iconCls="fa fa-folder-open-o" plain="true" method="expand">展开</a>
    </div>

    <div class="treegrid" fit="false" border="false" url="{{ module_url('system/role/module') }}"></div>

</div>

<script type="text/javascript">
    $(':module').options({
        toolbar: $('.toolbar', ':module'),
        treegrid: $('.treegrid', ':module'),
        data: {!! $data->toJson() !!},
        init: function () {
            this.event();
            this.initTreegrid();
        },
        event: function () {
            var self = this;
            $('[method]', ':module').on('click', function () {
                var method = $(this).attr('method');
                typeof self[method] === 'function' && self[method].call(self, this);
            });
        },
        initTreegrid() {
            var self = this;
            this.treegrid.treegrid({
                toolbar: self.toolbar,
                columns: [[
                    {field:'name',title:'名称',width:280,export:true},
                    {field:'group',title:'分组',width:100,export:true},
                    {field:'module',title:'模块',width:100,export:true},
                    {field:'alias',title:'别名',width:100,export:true}
                ]],
                height: '450px',
                checkbox: true,
                rownumbers: true,
                idField: 'key',
                treeField: 'name',
                animate: true,
                lines: true,
                onLoadSuccess: function() {
                    self.data.map(function(row) {
                        self.treegrid.treegrid('checkNode', row.key);
                        return row;
                    });
                }
            });
        },
        // 收缩
        collapse: function() {
            this.treegrid.treegrid('collapseAll');
        },
        // 展开
        expand: function() {
            this.treegrid.treegrid('expandAll');
        }
    }).init();

</script>

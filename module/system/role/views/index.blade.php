<div class="easyui-panel" title="系统管理/角色管理" fit="true" border="false" iconCls="fa fa-group">

    <div class="toolbar">
        <a class="easyui-linkbutton" iconCls="fa fa-plus" plain="true" method="form" url="{{ module_url('system/role/create', ['parent' => ':id']) }}">添加</a>
        <a class="easyui-linkbutton" iconCls="fa fa-edit" plain="true" method="form" url="{{ module_url('system/role/edit', ['id' => ':id']) }}"
            selected="true">编辑</a>
        <a class="easyui-linkbutton" iconCls="fa fa-trash" plain="true" method="destroy" url="{{ module_url('system/role/delete') }}">删除</a>
        <a class="easyui-linkbutton" iconCls="fa fa-filter" plain="true" method="filter">筛选</a>
        <a class="easyui-splitbutton" iconCls="fa fa-file-excel-o" plain="true" splitbutton="export">导出</a>
        <a class="easyui-splitbutton" iconCls="fa fa-print" plain="true" splitbutton="print" hide-xs>打印</a>
        <a class="easyui-linkbutton" iconCls="fa fa-folder-o" plain="true" method="collapse" hide-xs>收起</a>
        <a class="easyui-linkbutton" iconCls="fa fa-folder-open-o" plain="true" method="expand" hide-xs>展开</a>
    </div>

    <div class="splitbutton" style="display: none;">
        <div class="export">
            <div iconCls="fa fa-list-alt" method="export" export="list">导出列表</div>
            <div iconCls="fa fa-check-square-o" method="export" export="selected">导出选中</div>
        </div>

        <div class="print">
            <div iconCls="fa fa-list-alt" method="print" print="list">打印列表</div>
            <div iconCls="fa fa-check-square-o" method="print" print="selected">打印选中</div>
        </div>
    </div>

    <div class="treegrid" fit="true" border="false" url="{{ module_url('system/role') }}"></div>

    <div class="dialog"></div>

</div>

<script type="text/javascript">
    $(':host').options({
        treegrid: $('.treegrid', ':host'),
        toolbar: $('.toolbar', ':host'),
        dialog: $('.dialog', ':host'),
        filterbar: false,
        // 初始化
        init: function () {
            this.event();
            this.initSplitbutton();
            this.initTreegrid();
        },
        // 事件监听
        event: function () {
            var self = this;
            $('[method]', ':host').on('click', function () {
                var method = $(this).attr('method');
                typeof self[method] === 'function' && self[method].call(self, this);
            });
        },
        // 初始化下拉菜单
        initSplitbutton: function() {
            $('[splitbutton]', ':host').each(function() {
                var splitbutton = $(this).attr('splitbutton');
                var menu = $('.splitbutton > .' + splitbutton, ':host');
                menu && $(this).splitbutton({menu: menu});
            });
        },
        // 初始化数据列表
        initTreegrid: function() {
            var self = this;
            this.treegrid.treegrid({
                toolbar: self.toolbar,
                frozenColumns: [[
                    {field:'ck',checkbox:true},
                ]],
                columns: [[
                    {field:'role',title:'标识',width:64,sortable:true,export:true},
                    {field:'name',title:'角色',width:280,sortable:true,export:true},
                    {field:'created_at',title:'创建时间',width:150,sortable:true,export:true},
                    {field:'updated_at',title:'修改时间',width:150,sortable:true,export:true},
                ]],
                idField: 'id',
                treeField: 'name',
                animate: true,
                lines: true,
                onBeforeLoad: function(row, param){
                    if (!row) {
                        param.id = 0;
                    }
                },
                rownumbers: true,
                singleSelect: false,
                ctrlSelect: true,
                striped: true,
                multiSort: true
            });
        },
        // 表单操作
        form: function (e) {
            var self = this;
            var url = $(e).attr('url');
            var title = $(e).attr('title') || $(e).text();
            var iconCls = $(e).attr('iconCls');
            var width = $(e).attr('width');

            var row = this.treegrid.treegrid('getSelected');

            if(row) {
                url = url.replace(escape(':id'), row.id);
            } else {
                // 判断是否需要选中数据
                if($(e).attr('selected')) {
                    return;
                } else {
                    url = url.replace(escape(':id'), 0);
                }
            }

            this.dialog.dialog({
                title: title,
                iconCls: iconCls,
                modal: true,
                border: 'thin',
                width: width || '360px',
                constrain: true,
                href: url,
                onLoad: function() {
                    self.dialog.dialog('center');
                },
                buttons:[{
                    text: '保存',
                    iconCls: 'fa fa-save',
                    handler: function() {
                        var form = self.dialog.find('form');
                        if(!form) return;

                        form.form('ajax', {
                            progressbar: '数据发送中...',
                            url: url,
                            onSubmit: function () {
                                return $(this).form('validate');
                            },
                            success: function () {
                                $.messager.success('操作提示', '操作成功');
                                self.dialog.dialog('close');
                                self.treegrid.treegrid('reload');
                            },
                            error: '操作提示'
                        });

                    }
                },{
                    text: '取消',
                    iconCls: 'fa fa-close',
                    handler: function() {
                        self.dialog.dialog('close');
                    }
                }]
            });
        },
        // 删除
        destroy: function (e) {
            var rows = this.treegrid.treegrid('getSelections');
            if (rows.length) {
                var self = this;
                var url = $(e).attr('url');
                $.messager.confirm('操作确认', '确定要删除当前选中的' + rows.length +'条数据吗?', function (r) {
                    if (!r) return false;

                    var ids = rows.map(function(row) {
                        return row.id;
                    });
                    // ajax请求
                    $.post({
                        url: url,
                        type: 'POST',
                        data: {ids: ids},
                        success: function(){
                            self.treegrid.treegrid('reload');
                            $.messager.error('操作提示', '删除成功');
                        },
                        error: function(xhr) {
                            $.messager.error('操作提示', xhr.responseJSON ? xhr.responseJSON.message : '删除失败');
                        }
                    });


                });
            }
        },
        // 筛选
        filter: function () {
            this.filterbar = !this.filterbar;
            if (this.filterbar) {
                this.treegrid.treegrid('enableFilter', [{
                    field: 'role',
                    type: 'textbox',
                    op: ['equal', 'contains', 'notcontains', 'beginwith', 'endwith']
                }, {
                    field: 'name',
                    type: 'textbox',
                    op: ['equal', 'contains', 'notcontains', 'beginwith', 'endwith']
                }, {
                    field: 'created_at',
                    type: 'datetimebox',
                    op: ['equal', 'notequal', 'less', 'lessorequal', 'greater', 'greaterorequal']
                }, {
                    field: 'updated_at',
                    type: 'datetimebox',
                    op: ['equal', 'notequal', 'less', 'lessorequal', 'greater', 'greaterorequal']
                }]);
            } else {
                // 与datagrid.detailview插件冲突，必须重新赋值view
                try {
                    this.treegrid.treegrid('disableFilter');
                } catch (e) {
                }
            }
        },
        // 导出
        export: function (e) {
            var type = $(e).attr('export');
            var rows = null;
            switch(type) {
                case 'selected':
                    rows = this.treegrid.treegrid('getSelections');
                    break;
                default:
                    rows = this.treegrid.treegrid('getData') || [];
            }
            this.treegrid.treegrid('toExcel', {
                filename: '角色管理-' + moment().format('LLL') + '.xls',
                rows: rows
            });
        },
        // 打印
        print: function (e) {
            var type = $(e).attr('print');
            var rows = null;
            switch(type) {
                case 'selected':
                    rows = this.treegrid.treegrid('getSelections');
                    break;
                default:
                    rows = this.treegrid.treegrid('getData') || [];
            }
            this.treegrid.treegrid('print', {
                title: '角色管理',
                rows: rows
            });
        },
        // 收缩
        collapse: function() {
            var rows = this.treegrid.treegrid('getSelections');
            if(rows.length) {
                for(var i = 0; i < rows.length; i++) {
                    this.treegrid.treegrid('collapse', rows[i].id);
                }
            } else {
                this.treegrid.treegrid('collapseAll');
            }
        },
        // 展开
        expand: function() {
            var rows = this.treegrid.treegrid('getSelections');
            if(rows.length) {
                for(var i = 0; i < rows.length; i++) {
                    this.treegrid.treegrid('expand', rows[i].id);
                }
            } else {
                this.treegrid.treegrid('expandAll');
            }
        }
    }).init();

</script>

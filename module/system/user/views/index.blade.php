<div class="easyui-panel" title="系统管理/用户管理" fit="true" border="false" iconCls="fa fa-user">

    <div class="toolbar">
        <a class="easyui-linkbutton" iconCls="fa fa-plus" plain="true" method="create">添加</a>
        <a class="easyui-linkbutton" iconCls="fa fa-minus" plain="true" method="destroy">删除</a>
        <a class="easyui-linkbutton" iconCls="fa fa-filter" plain="true" method="filter">筛选</a>
        <a class="easyui-splitbutton" iconCls="fa fa-file-excel-o" plain="true" splitbutton="filter">导出</a>
        <a class="easyui-splitbutton" iconCls="fa fa-print" plain="true" splitbutton="print">打印</a>
    </div>

    <div class="splitbutton" style="display: none;">
        <div class="filter">
            <div iconCls="fa fa-list-alt" method="export" export="list">导出列表</div>
            <div iconCls="fa fa-check-square-o" method="export" export="selected">导出选中</div>
        </div>

        <div class="print">
            <div iconCls="fa fa-list-alt" method="print" print="list">打印列表</div>
            <div iconCls="fa fa-check-square-o" method="print" print="selected">打印选中</div>
        </div>
    </div>

    <div class="datagrid" fit="true" border="false" url="{{ module_url('system/user') }}"></div>

    <div class="dialog"></div>

</div>

<script type="text/javascript">
    $(':host').options({
        datagrid: $('.datagrid', ':host'),
        toolbar: $('.toolbar', ':host'),
        dialog: $('.dialog', ':host'),
        filterbar: false,
        // 初始化
        init: function () {
            this.event();
            this.initSplitbutton();
            this.initDatagrid();
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
        initDatagrid: function() {
            var self = this;
            this.datagrid.datagrid({
                // 默认参数
                toolbar: self.toolbar,
                frozenColumns: [[
                    {field:'ck',checkbox:true},
                ]],
                columns: [[
                    {field:'name',title:'姓名',width:120,sortable:true,export:true},
                    {field:'email',title:'邮箱',width:300,sortable:true,export:true},
                    {field:'created_at',title:'创建时间',width:150,sortable:true,export:true},
                    {field:'updated_at',title:'修改时间',width:150,sortable:true,export:true},
                ]],
                rownumbers: true,
                singleSelect: false,
                ctrlSelect: true,
                striped: true,
                pagination: true,
                pageList: [10,20,30,50,100,200,500,1000],
                multiSort: true,
                loadFilter: function (data) {
                    return {rows: data.data, total: data.total};
                },
                // 筛选参数
                clientPaging: false,
                remoteFilter: true,
                // detailview参数
                view: $.easyui.extension.datagrid.detailview,
                detailFormatter: function (index, row) {
                    return '<div class="form"></div>';
                },
                onExpandRow: function (index, row) {
                    if(!row.id) return;

                    var form = $(this).datagrid('getRowDetail', index).find('div.form');
                    form.panel({
                        border: false,
                        cache: true,
                        href: '{{ module_url('system/user/edit') }}?id=' + row.id,
                        onLoad: function () {
                            self.datagrid.datagrid('fixDetailRowHeight', index);
                            self.datagrid.datagrid('selectRow', index);
                            {{-- self.datagrid.datagrid('getRowDetail',index).find('form').form('load',row); --}}
                        }
                    });
                    self.datagrid.datagrid('fixDetailRowHeight', index);
                }
            });
        },
        // 添加
        create: function () {
            var self = this;

            this.dialog.dialog({
                title: '添加用户',
                iconCls: 'fa fa-plus',
                modal: true,
                border: 'thin',
                width: '360px',
                constrain: true,
                href: '{{ module_url('system/user/create') }}',
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
                            url: '{{ module_url('system/user/create') }}',
                            onSubmit: function () {
                                return $(this).form('validate');
                            },
                            success: function () {
                                $.messager.success('操作提示', '添加成功');
                                self.dialog.dialog('close');
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
        destroy: function () {
            var rows = this.datagrid.datagrid('getSelections');
            if (rows.length) {
                var self = this;
                $.messager.confirm('操作确认', '确定要删除当前选中的用户吗?', function (r) {
                    if (!r) return false;

                    // ajax请求
                    $.ajax({
                        url: '{{ module_url('system/user/delete') }}',
                        type: 'DELETE',
                        contentType:"application/json",
                        data: {ids: rows.map(function(row) {
                            return row.id;
                        })},
                        success: function(data){
                            rows
                                .map(function(row) {
                                    return self.datagrid.datagrid('getRowIndex', row);
                                })
                                .sort()
                                .reverse()
                                .map(function(index) {
                                    return self.datagrid.datagrid('deleteRow', index);
                                });
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
                this.datagrid.datagrid('enableFilter', [{
                    field: 'name',
                    type: 'textbox',
                    op: ['equal', 'notequal', 'contains', 'notcontains']
                }, {
                    field: 'email',
                    type: 'textbox',
                    options: {validType: {email: true}},
                    op: ['equal', 'notequal', 'contains', 'notcontains']
                }, {
                    field: 'created_at',
                    type: 'datetimebox',
                    op: ['equal', 'notequal', 'less', 'greater']
                }, {
                    field: 'updated_at',
                    type: 'datetimebox',
                    op: ['equal', 'notequal', 'less', 'greater']
                }]);
            } else {
                // 与datagrid.detailview插件冲突，必须重新赋值view
                try {
                    this.datagrid.datagrid('disableFilter');
                } catch (e) {
                }
                // 关闭筛选时清空条件
                this.datagrid.datagrid({
                    filterRules: [],
                    view: $.easyui.extension.datagrid.detailview
                });
            }
        },
        // 导出
        export: function (e) {
            var type = $(e).attr('export');
            var rows = null;
            switch(type) {
                case 'selected':
                    rows = this.datagrid.datagrid('getSelections');
                    break;
            }
            this.datagrid.datagrid('toExcel', {
                filename: '用户管理-' + moment().format('LLL') + '.xls',
                rows: rows
            });
        },
        // 打印
        print: function (e) {
            var type = $(e).attr('print');
            var rows = null;
            switch(type) {
                case 'selected':
                    rows = this.datagrid.datagrid('getSelections');
                    break;
            }
            this.datagrid.datagrid('print', {
                title: '用户管理',
                rows: rows
            });
        }
    }).init();

</script>

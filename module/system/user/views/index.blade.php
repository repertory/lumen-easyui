<div class="easyui-panel" title="系统管理/用户管理" fit="true" border="false" iconCls="fa fa-user">

    <div class="toolbar">
        <a class="easyui-linkbutton" iconCls="fa fa-plus" plain="true" method="form" url="{{ module_url('system/user/create') }}">添加</a>
        <a class="easyui-linkbutton" iconCls="fa fa-edit" plain="true" method="form" url="{{ module_url('system/user/edit', ['id' => ':id']) }}"
            selected="true">编辑</a>
        <a class="easyui-linkbutton" iconCls="fa fa-trash" plain="true" method="destroy" url="{{ module_url('system/user/delete') }}">删除</a>
        <a class="easyui-linkbutton" iconCls="fa fa-group" plain="true" method="form" url="{{ module_url('system/user/role', ['id' => ':id']) }}"
            selected="true">角色</a>
        <a class="easyui-linkbutton" iconCls="fa fa-universal-access" plain="true" method="acl" url="{{ module_url('system/user/acl', ['id' => ':id']) }}"
            hide-sm>权限</a>
        <a class="easyui-linkbutton" iconCls="fa fa-filter" plain="true" method="filter">筛选</a>
        <a class="easyui-splitbutton" iconCls="fa fa-file-excel-o" plain="true" splitbutton="export">导出</a>
        <a class="easyui-splitbutton" iconCls="fa fa-print" plain="true" splitbutton="print" hide-xs>打印</a>
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

    <div class="datagrid" fit="true" border="false" url="{{ module_url('system/user') }}"></div>

    <div class="dialog"></div>

</div>

<script type="text/javascript">
    $(':module').options({
        datagrid: $('.datagrid', ':module'),
        toolbar: $('.toolbar', ':module'),
        dialog: $('.dialog', ':module'),
        request: {!! $request->toJson() !!},
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
            $('[method]', ':module').on('click', function () {
                var method = $(this).attr('method');
                typeof self[method] === 'function' && self[method].call(self, this);
            });
        },
        // 初始化下拉菜单
        initSplitbutton: function() {
            $('[splitbutton]', ':module').each(function() {
                var splitbutton = $(this).attr('splitbutton');
                var menu = $('.splitbutton > .' + splitbutton, ':module');
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
                    {field:'roles',title:'角色',width:100,sortable:false,export:true,formatter:function(roles){
                        return roles.map(function(role){
                            return role.name;
                        }).join(',');
                    }},
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
                queryParams: self.request,
                // 筛选参数
                clientPaging: false,
                remoteFilter: true
            });
        },
        // 表单操作
        form: function(e) {
            var self = this;
            var url = $(e).attr('url');
            var title = $(e).attr('title') || $(e).text();
            var iconCls = $(e).attr('iconCls');
            var width = $(e).attr('width');

            var row = this.datagrid.datagrid('getSelected');

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
                width: width || 360,
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
                                self.datagrid.datagrid('reload');
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
            var rows = this.datagrid.datagrid('getSelections');
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
                        success: function() {
                            self.datagrid.datagrid('reload');
                            $.messager.error('操作提示', '删除成功');
                        },
                        error: function(xhr) {
                            $.messager.error('操作提示', xhr.responseJSON ? xhr.responseJSON.message : '删除失败');
                        }
                    });
                });
            }
        },
        // 权限
        acl: function(e) {
            var row = this.datagrid.datagrid('getSelected');
            if(!row) return;

            var self = this;
            var url = $(e).attr('url').replace(escape(':id'), row.id);
            var title = $(e).attr('title') || $(e).text();
            var iconCls = $(e).attr('iconCls');

            this.dialog.dialog({
                title: title,
                iconCls: iconCls,
                modal: true,
                border: 'thin',
                width: 640,
                constrain: true,
                href: url,
                onLoad: function() {
                    self.dialog.dialog('center');
                },
                buttons:[{
                    text: '保存',
                    iconCls: 'fa fa-save',
                    handler: function() {
                        var child = self.dialog.find('[module]');
                        if(!child) return;

                        var rows = child.options().treegrid.treegrid('getCheckedNodes');
                        var result = rows
                            .filter(function(row) {
                                if(row.group == '*' || row.module == '*' || row.alias == '*'){
                                    return true;
                                }
                                return !rows.some(function(item) {
                                    return $.inArray(item.key, [
                                        [row.group, '*', '*'].join('-'),
                                        [row.group, row.module, '*'].join('-')
                                    ]) > -1;
                                });
                            })
                            .filter(function(row) {
                                if(row.group == '*' && row.module == '*' && row.alias == '*'){
                                    return true;
                                }
                                return !rows.some(function(item) {
                                    return $.inArray(item.key, [
                                        [row.group, '*', '*'].join('-')
                                    ]) > -1;
                                });
                            })
                            .map(function(row) {
                                return {
                                    group: row.group,
                                    module: row.module,
                                    alias: row.alias
                                };
                            });

                        $.post({
                            url: url,
                            type: 'POST',
                            data: {acl: JSON.stringify(result)},
                            success: function() {
                                $.messager.success('操作提示', '操作成功');
                                self.dialog.dialog('close');
                            },
                            error: function(xhr) {
                                $.messager.error('操作提示', xhr.responseJSON ? xhr.responseJSON.message : '操作失败');
                            }
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
        // 筛选
        filter: function () {
            this.filterbar = !this.filterbar;
            if (this.filterbar) {
                this.datagrid.datagrid('enableFilter', [{
                    field: 'name',
                    type: 'textbox',
                    op: ['equal', 'contains', 'notcontains', 'beginwith', 'endwith']
                }, {
                    field: 'email',
                    type: 'textbox',
                    op: ['equal', 'contains', 'notcontains', 'beginwith', 'endwith']
                }, {
                    field: 'roles',
                    type: 'label'
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
                    this.datagrid.datagrid('disableFilter');
                } catch (e) {
                }
                // 关闭筛选时清空条件
                this.datagrid.datagrid({
                    filterRules: [],
                    clientPaging: false,
                    remoteFilter: true
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

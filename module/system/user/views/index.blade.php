<div class="easyui-panel" title="系统管理/用户管理" fit="true" border="false" iconCls="fa fa-user">

    <div class="toolbar">
        <a class="easyui-linkbutton" iconCls="fa fa-plus" plain="true" method="create">添加</a>
        <a class="easyui-linkbutton" iconCls="fa fa-minus" plain="true" method="destroy">删除</a>
        <a class="easyui-linkbutton" iconCls="fa fa-filter" plain="true" method="filter">筛选</a>
        <a class="easyui-splitbutton" splitbutton="filter" iconCls="fa fa-file-excel-o" plain="true">导出</a>
        <a class="easyui-splitbutton" splitbutton="print" iconCls="fa fa-print" plain="true">打印</a>
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

    <div class="datagrid" fit="true" border="false"></div>

</div>

<script type="text/javascript">
    $(':host').options({
        datagrid: $('.datagrid', ':host'),
        toolbar: $('.toolbar', ':host'),
        filterbar: false,
        init: function () {
            this.event();
            this.initSplitbutton();
            this.initDatagrid();
        },
        event: function () {
            var self = this;
            $('[method]', ':host').on('click', function () {
                var method = $(this).attr('method');
                typeof self[method] === 'function' && self[method].call(self, this);
            });
        },
        initSplitbutton: function() {
            $('[splitbutton]', ':host').each(function() {
                var splitbutton = $(this).attr('splitbutton');
                var menu = $('.splitbutton > .' + splitbutton, ':host');
                menu && $(this).splitbutton({menu: menu});
            });
        },
        initDatagrid: function() {
            var self = this;
            this.datagrid.datagrid({
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
                url: '{{ module_url('system/user') }}',
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
                clientPaging: false,
                remoteFilter: true,
                view: $.easyui.extension.datagrid.detailview,
                detailFormatter: function (index, row) {
                    return '<div class="form"></div>';
                },
                onExpandRow: function (index, row) {
                    var form = $(this).datagrid('getRowDetail', index).find('div.form');
                    form.panel({
                        border: false,
                        cache: true,
                        href: row.id ? '{{ module_url('system/user/edit') }}?id=' + row.id : '{{ module_url('system/user/create') }}',
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
        create: function () {
            this.datagrid.datagrid('appendRow', {isNewRecord: true});
            var index = this.datagrid.datagrid('getRows').length - 1;
            this.datagrid.datagrid('expandRow', index);
            this.datagrid.datagrid('selectRow', index);
        },
        destroy: function () {
            var rows = this.datagrid.datagrid('getSelections');
            if (rows.length) {
                var self = this;
                $.messager.confirm('操作确认', '确定要删除当前选中的用户吗?', function (r) {
                    if (!r) return false;

                    // ajax请求
                    {{--  $.ajax({
                        url: '{{ module_url('system/user/delete') }}',
                        type: 'DELETE',
                        contentType:"application/json",
                        data: {ids: rows.map(function(row) {
                            return row.id;
                        })},
                        success: function(data){
                            console.log(data);
                        },
                        error: function(err) {
                            console.error(err);
                        }
                    });  --}}

                    // 删除选中行
                    rows
                        .map(function(row) {
                            return self.datagrid.datagrid('getRowIndex', row);
                        })
                        .sort()
                        .reverse()
                        .map(function(index) {
                            return self.datagrid.datagrid('deleteRow', index);
                        });
                });
            }
        },
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

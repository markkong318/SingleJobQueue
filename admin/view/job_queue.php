<?php 
require_once 'lib/header.php';
?>
<script src="view/js/<?=basename($_SERVER["SCRIPT_NAME"], ".php") ?>.js"></script>
<script type="text/javascript">

$(document).ready(function(){
});
</script>

<div id="main_panel">
	<table>
		<tr>
			<td>
				最後更新日付 <span class="last_update"></span>
			</td>
			<td style="text-align: right !important;">
				更新頻度
				<select class="refresh_time">
					<option value="0">整理しない</option>
					<option value="10">10秒</option>
					<option value="60">60秒</option>
				</select>
			</td>
		</tr>
	</table>
	<br />
	<table class="job_table">
		<thead>
			<tr>
				<th width="1%">#</th>
				<th>Job ID</th>
				<th>名前</th>
				<th>環境</th>
				<th>状態</th>
				<th>予約</th>
				<th>最後更新</th>
				<th width="1%" style="white-space:nowrap;">操作</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	
	<script type="text/template" id="tpl_job_table">

		<td><%=i %></td>
		<td><%=job_id %></td>
		<td><%=name %></td>
		<td>
			<% if(command.indexOf("sandbox") > -1){ %>
				開発
			<% }else if(command.indexOf("honban") > -1){ %>
				<font color="red"><b>本番</b></font>
			<% }else{ %>
				未知
			<% } %>
		</td>
		<td>
			<% if(status == 0){ %>
				<font color='blue'><b>準備中</b></font>
			<% }else if(status == 1){ %>
				<font color='red'><b>実行中</b></font>
			<% }else if(status == 2){ %>
				<font color='green'><b>終了</b></font>
			<% }else{ %>
				<%=status %>
			<% } %>
		</td>
		<td>
			<% if(start_dt == null){ %>
				なし
			<% }else{ %>
				<%=start_dt %>
			<% } %>
		</td>
		<td><%=update_dt %></td>
		<td width="1%" style="white-space:nowrap;">
			<input type="button" value="詳細" class="detail" />
			<% if(status == 2){ %>
			<input type="button" value="ログ" class="log" />
			<% } %>
			<% if(status == 0){ %>
				<input type="button" value="削除" class="delete button_red"/>
			<% } %>
		</td>
	
	</script>
	
	<span class="pagination"></span>
</div>

<div id="detail_pane" class="reveal-modal">
	<h3>ジョブの詳細</h3>
	<table>
		<thead>
			<tr>
				<th width="30%">鍵</th>
				<th width="70%">値</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>ジョブID</td>
				<td><span class="job_id"></span></td>
			</tr>
			<tr>
				<td>名前</td>
				<td><span class="name"></span></td>
			</tr>
			<tr>
				<td>環境</td>
				<td><span class="env"></span></td>
			</tr>
			<tr>
				<td>コマンド</td>
				<td><span class="command"></span></td>
			</tr>
			<tr>
				<td>ログファイル</td>
				<td><span class="log_file"></span></td>
			</tr>
			<tr>
				<td>状態</td>
				<td><span class="status"></span></td>
			</tr>
			<tr>
				<td>PID</td>
				<td><span class="pid"></span></td>
			</tr>
			<tr>
				<td>予約</td>
				<td><span class="start_dt"></span></td>
			</tr>
			<tr>
				<td>最後更新</td>
				<td><span class="update_dt"></span></td>
			</tr>
		</tbody>
	</table>
	<input type="button" class="close" value="閉じる" />
</div>

<?php
require_once 'lib/footer.php';
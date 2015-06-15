$(function() {
	
	var App = {};
	App.Models = {};
	App.Collections = {};
	App.Views = {};
	Backbone.Relational.store.addModelScope(App.Models);
	Backbone.Relational.store.addModelScope(App.Collections);
	Backbone.Relational.store.addModelScope(App.Views);
	
	/**
	 * Job Detail Model
	 */
	App.Models.JobDetailModel = Backbone.Model.extend({
		initialize: function(){
			this.url = "?b=1&page_action=job_detail_model&id=" + this.get('id');
		},
	});
	
	/**
	 * Job Detail View
	 */
	App.Views.JobDetailView = Backbone.View.extend({
		el: "#detail_pane",
		
		events: {
			"click .close": "hide",
		},
		
		initialize: function(){
			
		},
	
		render: function(){
			
			//job id
			this.$(".job_id").html(this.model.get("job_id"));
			
			//name
			this.$(".name").html(this.model.get("name"));
			
			//env
			var command = this.model.get("command");
			if(command.indexOf("sandbox") > -1){
				this.$(".env").html("開発");
			}else if(command.indexOf("honban") > -1){
				this.$(".env").html("本番");
			}else{
				this.$(".env").html("未知");
			}
			
			//command
			this.$(".command").html(this.model.get("command"));
			
			//log file
			this.$(".log_file").html(this.model.get("log_file"));
			
			//status
			var status = this.model.get("status");
			if(status == 0){
				this.$(".status").html("準備中");
			}else if(status == 1){
				this.$(".status").html("実行中");
			}else if(status == 2){
				this.$(".status").html("終了");
			}else{
				this.$(".status").html(status);
			}
			
			//pid
			this.$(".pid").html(this.model.get("pid"));
			
			//start dt
			var start_dt = this.model.get("start_dt");
			if(start_dt == null){
				this.$(".start_dt").html("なし");
			}else{
				this.$(".start_dt").html(this.model.get("start_dt"));
			}
			
			//update dt
			this.$(".update_dt").html(this.model.get("update_dt"));
		},
		
		show: function(){
			$(this.el).reveal({
			    animation: 'fadeAndPop',                   //fade, fadeAndPop, none
			    animationspeed: 300,                       //how fast animtions are
			    closeonbackgroundclick: true,              //if you click background will modal close?
			    dismissmodalclass: 'close-reveal-modal'    //the class of a button or element that will close an open modal
			});
		},
		
		hide: function(){
			$(this.el).trigger('reveal:close');
		},
		
		setId: function(id, callback){
			var _this = this;
			
			this.model = undefined;
			
			this.model = new App.Models.JobDetailModel({id: id});
			this.model.fetch({
				success: function(collection, res, options){
					_this.render();
					callback();
				}
			})
		}
	});
	
	/**
	 * Job Page Model
	 */
	App.Models.JobPageModel = Backbone.RelationalModel.extend({
		defaults: {
			"currentPage": "1",
			"itemsOnPage": "15",
			"items": "0",
		},
		relations: [
		    {
		    	type: Backbone.HasMany,
				key: 'jobs',
				relatedModel: 'JobModel',
				collectionType: 'JobCollection',
		    },
		],
		
		initialize: function(){
			this.on("change:currentPage change:currentPage", this.onChangePage);
			
			this.setUrl(this.get("currentPage"), this.get("itemsOnPage"));
		},
		
		setUrl: function(currentPage, itemsOnPage){
			this.url = "?b=1&page_action=job_page_model&currentPage=" + this.get("currentPage") + "&itemsOnPage=" + this.get("itemsOnPage");
		},
		
		onChangePage: function(){			
			this.setUrl(this.get("currentPage"), this.get("itemsOnPage"));
		},
	});
	
	
	/**
	 * Job Model
	 */
	App.Models.JobModel = Backbone.RelationalModel.extend({
		defaults: {
		},
	
		initialize: function(){
			this.url = "?b=1&page_action=job_model&id=" + this.get('id');
		},
	});
	
	/**
	 * Job Collection
	 */
	App.Collections.JobCollection = Backbone.Collection.extend({
		model: App.Models.JobModel,
		url: "?b=1&page_action=get_jobs",
	});
	
	/**
	 * Job View
	 */
	App.Views.JobView = Backbone.View.extend({
		tagName: "tr",
		template: _.template($("#tpl_job_table").html()),
		
		events:{
			"click .log": "btnGetLog",
			"click .delete": "btnDeleteJob",
			"click .detail": "btnGetJobDetail",
		},
		
		render: function(){
			$(this.el).html(this.template(this.model.toJSON()));
			return this;
		},
		
		btnGetLog: function(){
			var filename = this.model.get('log_file');
			window.location.href = "job_queue.php?b=1&page_action=get_log&filename=" + filename;
		},
		
		btnDeleteJob: function(){
			this.model.destroy({wait:true});
		},
		
		btnGetJobDetail: function(){
			var id = this.model.get("id");
			
			App.detail_view.setId(id, function(){
				App.detail_view.show();
			});
		},
	});
	
	/**
	 * Main View
	 */
	App.Views.MainView = Backbone.View.extend({
		el: "#main_panel",
		
		initialize: function(options){
			var _this = this;
			
			_.bindAll(this, "onPageChange");
			
			this.model = new App.Models.JobPageModel();
			this.model.fetch({
				success: function(collection, res, options){
					App.router.navigate("page/" + _this.model.get("currentPage") + "/" + _this.model.get("itemsOnPage"));
					_this.render();
				},
			});
			
			this.listenTo(this.model.get("jobs"), "remove", this.onModelRemove);
		},
		
		render: function(){
			
			var _this = this;
			$(this.el).find(".job_table > tbody ").html("");
			
			var i = (this.model.get("currentPage") - 1) * this.model.get("itemsOnPage");
			_.each(this.model.get("jobs").models, function(item){
				i++;
				item.set("i", i);
				$(this.el).find(".job_table > tbody").append(new App.Views.JobView({model: item}).render().el);
			}, this);
			
			this.$(".pagination").pagination({
		        items: _this.model.get("items"),
		        itemsOnPage: _this.model.get("itemsOnPage"),
		        currentPage: _this.model.get("currentPage"),
		        onPageClick: _this.onPageChange,
		        cssStyle: 'light-theme'
		    });
			
			this.$(".last_update").html(this.model.get("last_update"));
			
			return this;
		},
		
		onModelRemove: function(){
			this.render();
		},
		
		onPageChange: function(pageNumber, event){
			console.log(this.model);
			this.model.set("currentPage", pageNumber);
			
			this.model.fetch({
				success: function(){
					App.router.navigate("page/" + _this.model.get("currentPage") + "/" + _this.model.get("itemsOnPage"));
					_this.render();
				},
			});
		},
	});
	
	/**
	 * App router
	 */
	var AppRouter = Backbone.Router.extend({
		routes: {
			"" : "defaultRoute",
			"page/:page/:count" : "showPage",
		},
		
		initialize: function(){
			App.main_view = new App.Views.MainView();
			App.detail_view = new App.Views.JobDetailView();
		},
	
		defaultRoute: function(){
			
		},
		
		showPage: function(page){
			
		},
	});
	
	
	App.router = new AppRouter();
	Backbone.history.start();
	
	
	
});

SirTrevor.Blocks.Code = SirTrevor.Block.extend({

	type: "code",

	title: function() { return 'Code'; },

	editorHTML: '<pre class="st-required st-text-block" style="text-align: left; font-size: 0.75em;" contenteditable="true"></pre><input type=text class="st-input-string js-caption-input" name=caption placeholder="Caption" style="width: 100%; margin-top: 10px; text-align: center">',

	icon_name: 'quote',

	loadData: function(data){
		this.getTextBlock().html(SirTrevor.toHTML(data.text, this.type));
		this.$('.js-caption-input').val(data.caption);
	}
});

<template>
	<div>
		表示するタグを選択してください
		<div class="tagbadge__wrapper">
			<div v-for="tag of localTagList" :key="`tagbd__${tag.id}`" @click="toggle(tag)">
				<span class="tagbadge" :style="{'background-color':tag.color}">
					<span class="tagtext" :style="{'color':tag.color}">{{ tag.tag_name }}</span>
				</span>
				<div />
			</div>
			<div @click="selectAll()">
				<span class="tagbadge" :style="{'background-color':colorAll}">
					<span class="tagtext" :style="{'color':colorAll}">全選択</span>
				</span>
			</div>
			<div @click="unSelectAll()">
				<span class="tagbadge" :style="{'background-color':colorNon}">
					<span class="tagtext" :style="{'color':colorNon}">全選択解除</span>
				</span>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'TagSelector',
	props: {
		tags: {
			type: Array,
			default: () => { return [] },
		},
		filter: {
			type: Object,
			default: () => { return {} },
		},
	},
	data() {
		return {
			localTagList: [],
		}
	},
	computed: {
		selectedTags() {
			return this.localTagList.filter((item) => item.selected).map((selected) => selected.id).join(',')
		},
		localFilter: {
			get() { return this.filter },
			set(val) { this.$emit('update:filter', val) },
		},
		selectedLength() {
			return this.localTagList.filter((item) => item.selected).length
		},
		colorAll() {
			if (this.selectedLength === this.localTagList.length) {
				return '#F00'
			} else {
				return '#666'
			}
		},
		colorNon() {
			if (!this.selectedLength) {
				return '#F00'
			} else {
				return '#666'
			}
		},

	},
	watch: {
		tags() {
			this.localTagList = this.tags.map((item) => {
				item.selected = true
				item.originalcolor = item.color
				return item
			})
		},
	},
	methods: {
		toggle(tag) {
			if (tag.selected) {
				tag.color = '#CCC'
				tag.selected = false
			} else {
				tag.color = tag.originalcolor
				tag.selected = true
			}
			this.localTagList = this.localTagList.map((localTag) => {
				if (localTag.id === tag.id) {
					return tag
				} else {
					return localTag
				}

			})
			this.updateFilter()
		},
		updateFilter() {
			const tmpFilter = Object.assign({}, this.filter)
			tmpFilter.tags = this.selectedTagsStr()
			this.localFilter = tmpFilter

		},
		selectedTagsStr() {
			return this.localTagList.filter((item) => item.selected).map((selected) => selected.id).join(',')
		},
		selectAll() {
			this.localTagList = this.localTagList.map((localTag) => {
				localTag.selected = true
				localTag.color = localTag.originalcolor
				return localTag
			})
			this.updateFilter()

		},
		unSelectAll() {
			this.localTagList = this.localTagList.map((localTag) => {
				localTag.selected = false
				localTag.color = '#CCC'
				return localTag
			})
			this.updateFilter()

		},
	},

}
</script>

<style scoped>
.tagbadge__wrapper{
	padding-inline: 3px;
	display:flex;
	flex-wrap: wrap;
	align-items: flex-start;
}

.tagbadge{
	padding:3px 6px;
	margin-right:8px;
	margin-left:1px;
	font-size:75%;
	border-radius:6px;
	box-shadow:0 0 3px #ddd;
	white-space:nowrap;
}

.tagtext{
	filter:invert(100%) grayscale(100%) contrast(100);
}
</style>

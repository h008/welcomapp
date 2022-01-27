<template>
	<div class="pagination__wrapper">
		<ul class="pagination">
			<li>
				<a @click="updateCurrent(1)">≪</a>
			</li>
			<li v-for="item of displayList" :key="`pa__${item}`">
				<a v-if="item===current" class="active" @click="updateCurrent(item)">{{ item }}</a>
				<a v-else @click="updateCurrent(item)">{{ item }}</a>
			</li>
			<li>
				<a @click="updateCurrent(total)">≫</a>
			</li>
		</ul>
	</div>
</template>
<script>
export default {
	name: 'Pagination',
	props: {
		total: {
			type: Number,
			default: 0,
		},
		current: {
			type: Number,
			default: 0,

		},
	},
	computed: {
		numList() {
			if (!this.total) { return [1] }
			return [...Array(this.total)].map((_, i) => i + 1)
		},
		displayList() {
			let startIndex = this.current - 4
			if (startIndex < 0) {
				startIndex = 0
			}
			let endIndex = startIndex + 7
			if (endIndex > this.total) {
				endIndex = this.total
				startIndex = endIndex - 7
			}
			if (startIndex < 0) {
				startIndex = 0
			}
			return this.numList.slice(startIndex, endIndex)

		},

	},
	methods: {
		updateCurrent(n) {
			console.info(n)
			this.$emit('update:current', n)
		},
	},
}
</script>
<style scoped>
ul.pagination{
	display:inline-block;
	padding:0;
	margin:0;
}

ul.pagination li{
	display:inline;
}

ul.pagination li a{
	color:black;
	float:left;
	padding:8px 16px;
	text-decoration:none;
	transition:background-color .3s;
	border:1px solid #ddd;
}

ul.pagination li a.active{
	background-color:#1B9FE7;
	color:white;
	border:1px solid #1B9FE7;
}

ul.pagination li a:hover:not(.active){
	background-color:#aaa;
}

div.pagination__wrapper{
	text-align:center;
}
</style>

<style>
    .progress.circular {
	appearance: none;
	box-sizing: border-box;
	width: 60px;
	height: 60px;
	background-color: #c4000000;
	padding: 0px;
	mask-image: linear-gradient(transparent 50%, white 50%),
		linear-gradient(to right, transparent 50%, white 50%);
	position: relative;
	overflow: hidden;
	display: block;
	color: #3b49df;
	animation: rotate 6s infinite;
}
.progress.circular:before {
	content: '';
	box-sizing: border-box;
	border: solid 0.25em transparent;
	border-top-color: currentColor;
	border-radius: 100px;
	background-color: transparent;
	animation: rotate-shrink 0.75s infinite linear alternate;
	display: block;
	height: 60px;
	width: 60px;
	position: absolute;
	top: 0;
	left: 0;
}
@keyframes rotate {
	0% {
		transform: rotate(0deg);
	}
	12.5% {
		transform: rotate(180deg);
		animation-timing-function: linear;
	}
	25% {
		transform: rotate(630deg);
	}
	37.5% {
		transform: rotate(810deg);
		animation-timing-function: linear;
	}
	50% {
		transform: rotate(1260deg);
	}
	62.5% {
		transform: rotate(1440deg);
		animation-timing-function: linear;
	}
	75% {
		transform: rotate(1890deg);
	}
	87.5% {
		transform: rotate(2070deg);
		animation-timing-function: linear;
	}
	100% {
		transform: rotate(2520deg);
	}
}
@keyframes rotate-shrink {
	0% {
		transform: rotate(-30deg);
	}
	29.4% {
		border-left-color: transparent;
	}
	29.41% {
		border-left-color: currentColor;
	}
	64.7% {
		border-bottom-color: transparent;
	}
	64.71% {
		border-bottom-color: currentColor;
	}
	100% {
		border-left-color: currentColor;
		border-bottom-color: currentColor;
		transform: rotate(225deg);
	}
}
</style>

<div class="d-flex justify-content-center mt-5">
    <div class="d-flex gap-5 mt-5 align-items-center">
        <div style="width: 200px;">
            <h3 class="text-primary">กำลังโหลด ...</h3>
            <h2 class="fw-lighter">กรุณารอสักครู่</h2>
        </div>
        <div class="progress circular"></div>
    </div>
</div>


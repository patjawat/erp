
<style>
   /* HTML: <div class="loader"></div> */
/* HTML: <div class="loader"></div> */
.loader {
  width: fit-content;
  font-weight: bold;
  font-family: monospace;
  font-size: 30px;
  background: radial-gradient(circle closest-side,#000 94%,#0000) right/calc(200% - 1em) 100%;
  animation: l24 1s infinite alternate linear;
}
.loader::before {
  content: "กำลังโหลดข้อมูล...";
  font-family: 'prompt', sans-serif;
  font-weight:400;
  line-height: 1em;
  color: #0000;
  background: inherit;
  background-image: radial-gradient(circle closest-side,#fff 94%,#000);
  -webkit-background-clip:text;
          background-clip:text;
}

@keyframes l24{
  100%{background-position: left}
}
@keyframes l4 {to{clip-path: inset(0 -1ch 0 0)}}
</style>

<div class="d-flex justify-content-center align-items-center mt-5">
    <div class="loader mt-5"></div>
</div>
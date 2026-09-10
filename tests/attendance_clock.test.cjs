const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');

// Unit harness only: no browser or real sensor access. Exercises the shipped form coordinator.
function harness(options = {}) {
    const fields = new Map(), handlers = {}, posts = [], store = new Map();
    const element = () => ({value:'',textValue:'',props:{},classes:new Set(['d-none']),
        text(v){if(v===undefined)return this.textValue;this.textValue=v;return this;},
        val(v){if(v===undefined)return this.value;this.value=v;return this;},
        prop(k,v){this.props[k]=v;return this;},trigger(){return this;},
        removeClass(v){v.split(' ').forEach(c=>this.classes.delete(c));return this;},
        addClass(v){v.split(' ').forEach(c=>this.classes.add(c));return this;},
        toggleClass(v,on){on?this.classes.add(v):this.classes.delete(v);return this;},
        one(){return this;}});
    function field(selector){if(!fields.has(selector))fields.set(selector,element());return fields.get(selector);}
    const form = {dataset:{}};
    const wrapper = {find:field,closest:()=>element(),on:(name,fn)=>{handlers[name]=fn;}};
    const $ = () => wrapper;
    $.ajax = args => {
        if(args.type!=='POST') {return {done(fn){fn({now:'2026-09-10 08:00:00',latest:null});return this;},fail(){return this;}};}
        posts.push({url:args.url,data:{...args.data}});
        if(args.url==='position')return Promise.resolve(options.position || {success:true,inside:true,location:'test'});
        return options.save ? options.save(args) : Promise.resolve({success:true,checkin_at:'2026-09-10 08:00:00',message:'บันทึกเวลาสำเร็จ',location:'test'});
    };
    let serial=0;
    const window={jQuery:$,isSecureContext:options.secure!==false,crypto:{randomUUID:()=>`request-00000000-${++serial}`},setInterval:()=>1};
    const context={window,document:{getElementById:()=>form,body:{contains:()=>true}},navigator:{geolocation:{getCurrentPosition:options.gps || (resolve=>resolve({coords:{latitude:0,longitude:0}}))}},
        sessionStorage:{getItem:k=>store.get(k),setItem:(k,v)=>store.set(k,v),removeItem:k=>store.delete(k)},Intl,Date,Promise,clearInterval(){}};
    vm.runInNewContext(fs.readFileSync(require.resolve('../web/js/attendance-clock.js'),'utf8'),context);
    window.AttendanceClock.mount('form',{saveUrl:'save',positionUrl:'position',shiftsUrl:'shifts',employeeId:1});
    return {posts,store,field,role:n=>field(`[data-role="${n}"]`),click(){handlers.submit({preventDefault(){}});},async settle(){await new Promise(resolve=>setImmediate(resolve));}};
}
test('one click gets GPS and saves without selecting direction or roster',async()=>{
    const h=harness();h.click();await h.settle();
    assert.deepEqual(h.posts.map(p=>p.url),['position','save']);
    assert.equal(h.posts[1].data.check_type,undefined);assert.equal(h.posts[1].data.roster_item_id,undefined);
    assert.match(h.role('result').textValue,/สำเร็จ/);assert.equal(h.role('submit').props.disabled,false);
});
test('outside requires a reason and explicit submission, without an early record',async()=>{
    const h=harness({position:{success:true,inside:false,location:'test'}});h.click();await h.settle();
    assert.equal(h.posts.filter(p=>p.url==='save').length,0);assert.equal(h.role('reason-panel').classes.has('d-none'),false);
    h.field('[name="out_of_location_reason"]').val('ออกปฏิบัติงาน');h.click();await h.settle();
    assert.equal(h.posts.filter(p=>p.url==='save').length,1);assert.match(h.role('result').textValue,/สำเร็จ/);
});
test('GPS denial sends no request and explains recovery',async()=>{
    const h=harness({gps:(_ok,fail)=>fail({code:1})});h.click();await h.settle();
    assert.equal(h.posts.length,0);assert.match(h.role('result').textValue,/เปิดสิทธิ์ตำแหน่ง/);assert.equal(h.role('submit').props.disabled,false);
});
test('pending GPS prevents repeated clicks from creating parallel submissions',async()=>{
    let resolveGps; const h=harness({gps:ok=>{resolveGps=ok;}});h.click();h.click();
    assert.equal(h.role('submit').props.disabled,true);resolveGps({coords:{latitude:0,longitude:0}});await h.settle();
    assert.equal(h.posts.filter(p=>p.url==='save').length,1);
});
test('uncertain save retries with the same idempotency key',async()=>{
    let attempt=0;const h=harness({save:()=>++attempt===1?Promise.reject({status:0}):Promise.resolve({success:true,checkin_at:'2026-09-10 08:00:00',message:'บันทึกไว้แล้ว ไม่สร้างรายการซ้ำ'})});
    h.click();await h.settle();assert.match(h.role('result').textValue,/ยังยืนยันผลบันทึกไม่ได้/);
    h.click();await h.settle();const saves=h.posts.filter(p=>p.url==='save');
    assert.equal(saves[0].data.request_id,saves[1].data.request_id);assert.match(h.role('result').textValue,/ไม่สร้างรายการซ้ำ/);assert.equal(h.store.size,0);
});
test('expired login gives a login recovery message',async()=>{
    const h=harness({save:()=>Promise.reject({status:401})});h.click();await h.settle();assert.match(h.role('result').textValue,/เข้าสู่ระบบใหม่/);
});
test('insecure mobile website explains HTTPS requirement',async()=>{
    const h=harness({secure:false});h.click();await h.settle();assert.equal(h.posts.length,0);assert.match(h.role('result').textValue,/HTTPS/);
});

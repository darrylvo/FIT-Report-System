var ___test = -1;
QUnit.module("Mysql Connect & Clear Test");
QUnit.test( "Connect & Clear", function( assert ) {
   var done = assert.async();
   var ajax = $.ajax({
      type: "POST",
      url: "src/tests.php",
      error : function() {aseert.equal("false", "error");},
      success : function() {
         assert.equal(ajax.responseText, "succ");
         done();
      }
   });
});
___test = -1;
QUnit.module("Report.js Tests");
QUnit.test( "Register One Name Test", function( assert ) {

   var done = assert.async();
   var nameForm = $("<form>");
   var name = $("<input>").attr("id","cname").attr("name","namereg").val("testName0");
   nameForm.append(name);
   registerName(nameForm);
   setTimeout(function() {
      assert.equal(___test,"succ");
      done();},500);
});

___test = -1;
QUnit.test( "Duplicate Name Check", function( assert ) {

   var done = assert.async();
   var nameForm = $("<form>");
   var name = $("<input>").attr("id","cname").attr("name","namereg").val("testName0");
   nameForm.append(name);
   registerName(nameForm);
   setTimeout(function() {
      assert.equal(___test,"error");
      done();},500);
});

___test = -1;
QUnit.test( "Register Two more Names Test", function( assert ) {

   var done = assert.async(2);
   var nameForm = $("<form>");
   var name = $("<input>").attr("id","cname").attr("name","namereg").val("testName1");
   nameForm.append(name);
   registerName(nameForm);
   setTimeout(function() {
      assert.equal(___test,"succ");
      done();},500);

   name.val("testName2");
   registerName(nameForm);
   setTimeout(function() {
      assert.equal(___test,"succ");
      done();},500);
});


___test = -1;
QUnit.test( "Populate Name Selectbox Test", function( assert ) {
   var done = assert.async(1);
   var select = $("<select>").attr("id","sel");
   updateNames(select);
   setTimeout(function() {
      assert.equal(3,$(select).children('option').length);
      done();},500);
});
___test = -1;
QUnit.test( "Send Report (Form) Test *ONLY SENDS BLANK FORM", function( assert ) {
   var done = assert.async();
   globalForm = $("<form>");
   var coords = {"coords" : { "latitude" : 0, "longitude" : 0}};
   saveCoords(coords);
   setTimeout(function() {
      assert.equal("succ",___test);
      done();},500);
});

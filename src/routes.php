<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
// prepare เป็นการเตรียมคำสั่ง sql
// execute เปรียบเหมือนการ query
// fecthAll เปรียบเหมือนการ fetch_array

// ดึงข้อมูลทุกเรคอร์ด
$app->get('/product',function($request, $response, $args)
  {
    $sth = $this->db->prepare('SELECT * from product order by product_id desc');
    //ตั้งตัวแปร sth เก็บค่าคำสั่ง sql (เลือกข้อมูลทั้งหมด จากตาราง product โดยเรียง รหัสสินค้า จากมากไปหาน้อย (เพื่อให้แสดงข้อมูลค่าสุดไว้บนสุด))
    $sth->execute();  // สั่ง run  sql
    $products = $sth->fetchAll(); 
    //fetchAll = fetch_array คืนค่าเป็น array เก็บข้อมูลที่ได้ไว้ในตัวแปร products
    return $this->response->withJson($products); //return values ในรูปแบบของ Json
  }
);
// ดึงข้อมูลเฉพาะเรคอร์ดที่มีค่า primary key ตรงกับเงื่อนไข
$app->get('/product/[{id}]',function($request, $response, $args)
  {
    $sth = $this->db->prepare('SELECT * from product where product_id=:id'); 
    // id หลัง : เป็นพารามิเตอร์ ต้องให้ตรงกับ /product/[{id}] , sql ให้เลือกข้อมูลสินค้าที่มีรหัสสินค้าตรงกับ พารามิเตอร์ id
    $sth->bindParam("id",$args['id']); //bindParam คือการระบุค่าจาก $args['id'] ให้พารามิเตอร์ id
    $sth->execute();
    $products = $sth->fetchAll(); //เมื่อ execute แล้ว fetch จะได้ค่าเพียงค่าเดียวสามารถนำไปใช้งานได้เลยไม่ต้องทำการวนรอบ
    return $this->response->withJson($products);
  }
);
//การค้นหาข้อมูล  ในที่นี้ยกตัวอย่างการค้นหาด้วยชื่อสินค้า
$app->get('/product/search/[{query}]',function($request, $response, $args)
  {
    $sth = $this->db->prepare('SELECT * from product where product_name like:q'); 
    // id หลัง : เป็นพารามิเตอร์ ต้องให้ตรงกับ /product/[{id}] , sql ให้เลือกข้อมูลสินค้าที่มีรหัสสินค้าตรงกับ พารามิเตอร์ id
    $querys="%".$args['query']."%"; //ตั้งตัวแปร querys เพื่อสร้างเงื่อนไขการค้นหา %คำที่ต้องการค้นหา%
    $sth->bindParam("q",$querys); 
    $sth->execute();
    $products = $sth->fetchAll(); 
    return $this->response->withJson($products);
  }
);
//การเพิ่มข้อมูลลงฐานข้อมูล  ใช้ method = POST
$app->post('/addproduct',function($request, $response)
{
    $input=$request->getParsedBody();//สั่งให้รับค่าจาก form หน้า html มาเก็บไว้้ใน input
    $sqlinsert="INSERT into product (product_id, product_name, product_price, product_detail) 
    values (:pro_id, :pro_name, :pro_price, :pro_detail)";
    $sth=$this->db->prepare($sqlinsert);
    //ต่อไปนี้มีค่าที่ต้องการใส่ในตารางกี่ค่าให้ทำการ bindParam ค่าจาก form ไปให้ตัวแปรในวงเล็บ values
    $sth->bindParam("pro_id",$input['proid']); //proid คือชื่อ object จาก form ในหน้า html
    $sth->bindParam("pro_name",$input['proname']); //proname คือชื่อ object จาก form ในหน้า html
    $sth->bindParam("pro_price",$input['proprice']); //proprice คือชื่อ object จาก form ในหน้า html
    $sth->bindParam("pro_detail",$input['prodetail']); //prodetail คือชื่อ object จาก form ในหน้า html
    $sth->execute();
    
    // $input['id']=$this->db->lastInsertID();  //ใช้ใส่ข้อมูลที่เป็น auto หากในฐานข้อมูลไม่มี attribute ที่เป็น auto ก็ไม่ต้องใส่

    $result=array('msg'=>true); //ส่งค่า true ไปเก็บเป็น msgbox ในรุปแบบ array เก็บไว่้ในตัวแปร result
    return $this->response->withJson($result);
} 
);
// API สำหรับการลบข้อมูล Delete
$app->delete('/delproduct/[{id}]',function($request, $response, $args)
{
    $sth = $this->db->prepare("DELETE from product where product_id=:id"); 
    // id หลัง : เป็นพารามิเตอร์ ต้องให้ตรงกับ /DelProduct/[{id}] , sql ให้เลือกข้อมูลสินค้าที่มีรหัสสินค้าตรงกับ พารามิเตอร์ id
    $sth->bindParam("id",$args['id']); //bindParam คือการระบุค่าจาก $args['id'] ให้พารามิเตอร์ id
    $sth->execute();
    $result=array('msg'=>true); //ส่งค่า true ไปเก็บเป็น msgbox ในรุปแบบ array เก็บไว่้ในตัวแปร result
    return $this->response->withJson($products);
} 
);
//การแก้ไขข้อมูลลงฐานข้อมูล Update  ใช้ method = POST
$app->post('/updateproduct/[{id}]',function($request, $response, $args)
{
    $input=$request->getParsedBody();//สั่งให้รับค่าจาก form หน้า html มาเก็บไว้้ใน input
    $sqlupdate="UPDATE product set product_id=:pro_id, product_name=:pro_name, product_price=:pro_price, product_detail=:pro_detail
    where product_id=:id";
    $sth=$this->db->prepare($sqlupdate);
    //ต่อไปนี้มีค่าที่ต้องการใส่ในตารางกี่ค่าให้ทำการ bindParam ค่าจาก form ไปให้ตัวแปรในวงเล็บ values
    $sth->bindParam("pro_id",$input['proid']); //proid คือชื่อ object จาก form ในหน้า html
    $sth->bindParam("pro_name",$input['proname']); //proname คือชื่อ object จาก form ในหน้า html
    $sth->bindParam("pro_price",$input['proprice']); //proprice คือชื่อ object จาก form ในหน้า html
    $sth->bindParam("pro_detail",$input['prodetail']); //prodetail คือชื่อ object จาก form ในหน้า html
    $sth->execute();
    $result=$agrs['id']; 
    return $this->response->withJson($result);
} 
);
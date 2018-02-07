<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Storage;

class HotelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

		$xmlArrayData = $this->getArrayFromXmlFile();
		$jsonArrayData = $this->getArrayFromJsonFile();
		
		$data = array_merge($xmlArrayData['HOTELS']['HOTEL'],$jsonArrayData['HOTELS']['HOTEL']);
		
		echo '<pre>';
		print_r($data);
		die();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
	
	
	public function getArrayFromXmlFile(){
		
		$file_path = 'Files'.DIRECTORY_SEPARATOR.'1'.DIRECTORY_SEPARATOR.'Xml Hotels Result.xml';
		$contents = Storage::get($file_path);
		$ob = simplexml_load_string($contents);
		$parseData = json_decode(json_encode($ob),TRUE);
		
		return $parseData;
	}

	public function applyFilters($parseData){
		
	$filter = Input::get();
			
	$dataReturn = ["HOTELS" => ["HOTEL" => [] ]];
		foreach($parseData as $pd){
				if(isset($pd['HOTEL'])){
					
					foreach($pd['HOTEL'] as $hotel){
							$hotelInserted = TRUE;
							if(isset($hotel['@attributes'])){
								
								
									// Filter to apply on Hotels
									if(isset($filter['HotelName']) AND $filter['HotelName'] != ''){
									
										if(isset($hotel['@attributes']['HOTEL_NAME'])){
											if(strpos($hotel['@attributes']['HOTEL_NAME'],$filter['HotelName']) !== false){	
												$hotelInserted = TRUE;
											}else{
												$hotelInserted = FALSE;
											}
										}
										
									}
									
									if(isset($filter['HotelRating']) AND $filter['HotelRating'] != '' AND $hotelInserted){
										
										if(isset($hotel['@attributes']['RATING'])){
											
											
											
											if((int)$filter['HotelRating'] == (int)$hotel['@attributes']['RATING']){
												$hotelInserted = TRUE;
											}else{
												$hotelInserted = FALSE;
											}
										}
										
									}
									
									if($hotelInserted){
										$dataReturn["HOTELS"]["HOTEL"][] = $hotel;
									}
									
									// Filter to apply on Rooms
									if(isset($filter['IsReady']) AND $filter['IsReady'] != ''){
										
										if($hotelInserted){
											$totalHotels = count($dataReturn["HOTELS"]["HOTEL"]);
											$lastInsertedHotelIndex = $totalHotels - 1;
											
	
												$rooms = ["ROOM" => [] ];
												
												if(isset($dataReturn["HOTELS"]["HOTEL"][$lastInsertedHotelIndex]['ROOMS'])){
													
													if(isset($dataReturn["HOTELS"]["HOTEL"][$lastInsertedHotelIndex]['ROOMS']['ROOM'])){
														
														
														foreach($dataReturn["HOTELS"]["HOTEL"][$lastInsertedHotelIndex]['ROOMS']['ROOM'] as $room){
															if(isset($room['ROOM_STATUS']) AND $room['ROOM_STATUS'] == 'AVAILABLE'){
																
																$rooms['ROOM'][] = $room;
																
															}
														}
														
													}
													
													
												}
												
												$dataReturn["HOTELS"]["HOTEL"][$lastInsertedHotelIndex]['ROOMS'] = $rooms;
											
										}
									}
								
								
							}
						
					}
					
				}
				
		}
		
		return $dataReturn;	
	}
	
	
	
	public function getArrayFromJsonFile(){
		
		$contents = Storage::get('Files'.DIRECTORY_SEPARATOR.'1'.DIRECTORY_SEPARATOR.'JSON Hotels Result.json');
		$contents = json_decode($contents,TRUE);
		$contents = $this->jsonToXml($contents);
		
		return $contents;
	}
	
	public function jsonToXml($content){
		
		$dataReturn = ["HOTELS" => ["HOTEL" => [] ]];
		
		if(isset($content['avaliabilitiesResponse']) AND isset($content['avaliabilitiesResponse']['Hotels']) AND isset($content['avaliabilitiesResponse']['Hotels']['Hotel'])){	
			foreach($content['avaliabilitiesResponse']['Hotels']['Hotel'] as $hotel){
				$h = [
					'@attributes' => [
                                    'HOTEL_ID' => $hotel['HotelCode'],
                                    'HOTEL_NAME' => $hotel['HotelsName'],
                                    'LOCATION' => $hotel['Location'],
                                    'RATING' => $hotel['Rating'],
                                    'AVAILABLE' => $hotel['IsReady'],
                                    'STARTING_PRICE' => $hotel['LowestPrice'],
                                    'CURRENCY' => $hotel['Currency']
                                ]

								
				];
				
				$r = [
					"ROOMS" => ["ROOM" => [] ]
				];
				
				if(isset($hotel['AvailableRooms']) AND isset($hotel['AvailableRooms']['AvailableRoom'])){
					
					if(isset($hotel['AvailableRooms']['AvailableRoom']['RoomCode'])){
						$r["ROOMS"]["ROOM"][] = [
							'ROOMID' => ((isset($room['RoomCode']))?$room['RoomCode']:''),
							'ROOM_NAME' => ((isset($room['RoomName']))?$room['RoomName']:''),
							'OCCUPANCY' => ((isset($room['Occupancy']))?$room['Occupancy']:''),
							'ROOM_STATUS' => ((isset($room['Status']) AND $room['Status'] == 'true')?"AVAILABLE":'')
						];
					}else{
						foreach($hotel['AvailableRooms']['AvailableRoom'] as $room){

							$r["ROOMS"]["ROOM"][] = [
						'ROOMID' => ((isset($room['RoomCode']))?$room['RoomCode']:''),
								'ROOM_NAME' => ((isset($room['RoomName']))?$room['RoomName']:''),
								'OCCUPANCY' => ((isset($room['Occupancy']))?$room['Occupancy']:''),
								'ROOM_STATUS' => ((isset($room['Status']) AND $room['Status'] == 'true')?"AVAILABLE":'')
							];
						}
					}
				}
				
				$dataReturn['HOTELS']['HOTEL'][] = $h + $r;
			}
			
			
		}
		
		return $dataReturn;
		
		
	}
}

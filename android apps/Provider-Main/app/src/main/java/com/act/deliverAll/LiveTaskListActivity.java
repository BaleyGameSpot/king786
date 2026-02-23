package com.act.deliverAll;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.deliverAll.OrderListRecycleAdapter;
import com.fragments.MyBookingFragment;
import com.fragments.MyProfileFragment;
import com.fragments.MyWalletFragment;
import com.general.PermissionHandlers;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.AddBottomBar;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.model.deliverAll.liveTaskListDataModel;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;

import org.apache.commons.lang3.StringUtils;
import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 17-04-18.
 */

public class LiveTaskListActivity extends ParentActivity implements OrderListRecycleAdapter.OnItemClickListener {

    MTextView titleTxt;
    ImageView menuImgView, backImgView;
    /*Pagination*/
    boolean mIsLoading = false;
    boolean isNextPageAvailable = false;
    String next_page_str = "";
    ProgressBar loading_order_list;
    MTextView noOrderTxt;
    ErrorView errorView;
    String iOrderId = "";
    HashMap<String, String> data_trip;
    String vImage;
    private OrderListRecycleAdapter orderListRecycleAdapter;
    private ArrayList<liveTaskListDataModel> list = new ArrayList<>();
    private RecyclerView orderListRecyclerView;
    public AddBottomBar addBottomBar;
    FrameLayout containerView;
    boolean iswalletFragemnt = false;
    boolean isbookingFragemnt = false;
    String eBuyAnyService = "No";
    String GenieOrderType = "No";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_live_tasks);
        this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        if (data_trip == null) {
            if (!MyApp.getInstance().isGetDetailCall) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_TRY_AGAIN_TXT"), "", generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), i -> finish());
            }
            return;
        }
        if (savedInstanceState != null) {
            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
            }
        }

        iOrderId = data_trip.get("iOrderId");

        initView();
        setLabels();
        getAllLiveTasks();

        addBottomBar = new AddBottomBar(getActContext(), generalFunc, obj_userProfile, (LinearLayout) findViewById(R.id.rduTopArea));
        GetLocationUpdates.getInstance().setTripStartValue(true, true, true, data_trip.get("iTripId"));
        PermissionHandlers.getInstance().initiatePermissionHandler();
    }


    @Override
    protected void onResume() {
        super.onResume();

        if (myWalletFragment != null && iswalletFragemnt) {
            myWalletFragment.onResume();
        }

        if (myBookingFragment != null && isbookingFragemnt) {
            myBookingFragment.onResume();
        }
    }

    private void initView() {

        containerView = findViewById(R.id.containerView);
        titleTxt = findViewById(R.id.titleTxt);
        backImgView = findViewById(R.id.backImgView);
        menuImgView = findViewById(R.id.menuImgView);
        backImgView.setVisibility(View.GONE);
        menuImgView.setVisibility(View.GONE);
        orderListRecyclerView = findViewById(R.id.orderListRecyclerView);
        noOrderTxt = findViewById(R.id.noOrderTxt);
        loading_order_list = findViewById(R.id.loading_order_list);
        errorView = findViewById(R.id.errorView);
    }

    MyProfileFragment myProfileFragment;
    MyWalletFragment myWalletFragment;
    public MyBookingFragment myBookingFragment;

    public void openProfileFragment() {
        iswalletFragemnt = false;
        isbookingFragemnt = false;
        containerView.setVisibility(View.VISIBLE);
        if (myProfileFragment == null) {
            myProfileFragment = new MyProfileFragment();
        }

        getSupportFragmentManager().beginTransaction()
                .replace(containerView.getId(), myProfileFragment).commit();


    }

    public void openWalletFragment() {

        iswalletFragemnt = true;
        isbookingFragemnt = false;
        containerView.setVisibility(View.VISIBLE);
        if (myWalletFragment == null) {
            myWalletFragment = new MyWalletFragment();
        }

        getSupportFragmentManager().beginTransaction()
                .replace(containerView.getId(), myWalletFragment).commit();
    }

    public void openBookingFrgament() {

        iswalletFragemnt = false;
        isbookingFragemnt = true;

        containerView.setVisibility(View.VISIBLE);
        if (myBookingFragment != null) {
            myBookingFragment.onDestroy();
        }
        myBookingFragment = new MyBookingFragment();
        getSupportFragmentManager().beginTransaction()
                .replace(containerView.getId(), myBookingFragment).commit();
    }


    public void manageHome() {
        iswalletFragemnt = false;
        isbookingFragemnt = false;
        containerView.setVisibility(View.GONE);
    }


    private void getAllLiveTasks() {
        list = new ArrayList<>();
        orderListRecycleAdapter = new OrderListRecycleAdapter(getActContext(), list, generalFunc, false);
        orderListRecyclerView.setAdapter(orderListRecycleAdapter);
        orderListRecycleAdapter.notifyDataSetChanged();
        orderListRecycleAdapter.setOnItemClickListener(this);
        orderListRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
                                                      @Override
                                                      public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                                                          super.onScrolled(recyclerView, dx, dy);

                                                          int visibleItemCount = recyclerView.getLayoutManager().getChildCount();
                                                          int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                                                          int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();
                                                          int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                                                          if ((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable) {
                                                              mIsLoading = true;
                                                              orderListRecycleAdapter.addFooterView();
                                                              getLiveTaskOrderList(true);
                                                          } else if (!isNextPageAvailable) {
                                                              orderListRecycleAdapter.removeFooterView();
                                                          }
                                                      }
                                                  }

        );

        getLiveTaskOrderList(false);
    }

    public void getLiveTaskOrderList(final boolean isLoadMore) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        if (loading_order_list.getVisibility() == View.GONE) {
            loading_order_list.setVisibility(View.VISIBLE);
        }


        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetLiveTaskDetailDriver");
        parameters.put("iOrderId", iOrderId);
        if (isLoadMore) {
            parameters.put("page", next_page_str);
        }
        parameters.put("eSystem", Utils.eSystem_Type);

        noOrderTxt.setVisibility(View.GONE);
        list.clear();
        ApiHandler.execute(getActContext(), parameters, responseString -> {

            noOrderTxt.setVisibility(View.GONE);
            closeLoader();
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {


                if (generalFunc.checkDataAvail(Utils.action_str, responseStringObject)) {

                    String nextPage = generalFunc.getJsonValueStr("NextPage", responseStringObject);

                    JSONArray msgArray_obj = generalFunc.getJsonArray("message", responseStringObject);


                    for (int i = 0; i < msgArray_obj.length(); i++) {

                        JSONObject msg_obj = generalFunc.getJsonObject(msgArray_obj, i);


                        // User's Details Add
                        liveTaskListDataModel model = new liveTaskListDataModel();

                        // Restaurant's Details Add
                        eBuyAnyService = generalFunc.getJsonValueStr("eBuyAnyService", msg_obj);
                        GenieOrderType = generalFunc.getJsonValueStr("GenieOrderType", msg_obj);

                        /*Set Order detail*/
                        model.setPickedFromRes(generalFunc.getJsonValueStr("PickedFromRes", msg_obj));
                        model.seteBuyAnyService(eBuyAnyService);
                        model.setGenieOrderType(GenieOrderType);
                        model.setOrderNumber(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vOrderNo", msg_obj)));
                        model.setIsPhotoUploaded(generalFunc.getJsonValueStr("isPhotoUploaded", msg_obj));
                        model.setvVehicleType(generalFunc.getJsonValueStr("vVehicleType", msg_obj));




                        /*Set Restaurant Detail Model*/
                        model.setRestaurantName(generalFunc.getJsonValueStr("vCompany", msg_obj));
                        model.setRestaurantLattitude(generalFunc.getJsonValueStr("vRestuarantLocationLat", msg_obj));
                        model.setRestaurantLongitude(generalFunc.getJsonValueStr("vRestuarantLocationLong", msg_obj));
                        model.setRestaurantNumber(generalFunc.getJsonValueStr("vPhoneRestaurant", msg_obj));
                        model.setRestaurantId(generalFunc.getJsonValueStr("iCompanyId", msg_obj));
                        model.setRestaurantImage(generalFunc.getJsonValueStr("vRestuarantImage", msg_obj));
                        model.setIsCurrentTask(generalFunc.getJsonValueStr("isCurrentTask", msg_obj));
                        model.setCompanyRating(generalFunc.getJsonValueStr("CompanyAvgRating", msg_obj));


                        String restAddress = generalFunc.getJsonValueStr("vRestuarantLocation", msg_obj);
                        model.setRestaurantAddress(Utils.checkText(restAddress) ? StringUtils.capitalize(restAddress) : restAddress);


                        /*User Detail Model 2*/
                        //  model2.setUserName(generalFunc.getJsonValueStr("UserName", msg_obj));
                        String userAddress = generalFunc.getJsonValueStr("UserAdress", msg_obj);

                        /*User Detail*/
                        model.setUserName(generalFunc.getJsonValueStr("UserName", msg_obj));
                        model.setUserAddress(Utils.checkText(userAddress) ? StringUtils.capitalize(userAddress) : userAddress);
                        model.setUserLattitude(generalFunc.getJsonValueStr("vLatitude", msg_obj));
                        model.setUserLongitude(generalFunc.getJsonValueStr("vLongitude", msg_obj));
                        model.setUserNumber(generalFunc.getJsonValueStr("vPhoneUser", msg_obj));
                        model.setUserRating(generalFunc.getJsonValueStr("UserAvgRating", msg_obj));
                        model.setUserPPicName(generalFunc.getJsonValueStr("PPicName", msg_obj));

                        /*Set Lables*/
                        model.setLBL_CALL_TXT(generalFunc.retrieveLangLBl("Call", "LBL_CALL_TXT"));
                        model.setLBL_NAVIGATE(generalFunc.retrieveLangLBl("Navigate", "LBL_NAVIGATE"));
                        model.setLBL_PICKUP(generalFunc.retrieveLangLBl("Pickup", "LBL_PICKUP"));
                        model.setLBL_DELIVER(generalFunc.retrieveLangLBl("Deliver", "LBL_DELIVER"));
                        model.setLBL_CURRENT_TASK_TXT(generalFunc.retrieveLangLBl("Current Task", "LBL_CURRENT_TASK_TXT"));
                        model.setLBL_NEXT_TASK_TXT(generalFunc.retrieveLangLBl("Next Task", "LBL_NEXT_TASK_TXT"));
                        model.setIsForPickup(generalFunc.getJsonValue("isForPickup", msg_obj).toString());
                        model.setIsForDropoff(generalFunc.getJsonValue("isForDropoff", msg_obj).toString());
                        model.setLiveTaskStatus(generalFunc.getJsonValue("liveTaskStatus", msg_obj).toString());
                        model.setiOrderId(generalFunc.getJsonValue("iOrderId", msg_obj).toString());
                        model.setiTripId(generalFunc.getJsonValue("iTripId", msg_obj).toString());
                        model.setPassengerId(generalFunc.getJsonValue("passengerId", msg_obj).toString());
                        model.setPPicName(generalFunc.getJsonValue("PPicName", msg_obj).toString());
                        model.setvLastName(generalFunc.getJsonValue("vLastName", msg_obj).toString());


                        if (generalFunc.getJsonValueStr("PickedFromRes", msg_obj).equalsIgnoreCase("No") || (generalFunc.getJsonValueStr("PickedFromRes", msg_obj).equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("isPhotoUploaded", msg_obj).equalsIgnoreCase("No") && eBuyAnyService.equalsIgnoreCase("No"))) {
                            model.setIsRestaurant("Yes");
                        } else {
                            model.setIsRestaurant("NO");
                        }
                        list.add(model);
                    }

                    if (!nextPage.equals("") && !nextPage.equals("0")) {
                        next_page_str = nextPage;
                        isNextPageAvailable = true;
                    } else {
                        removeNextPageConfig();
                    }

                    orderListRecycleAdapter.notifyDataSetChanged();

                } else {
                    if (list.size() == 0) {
                        removeNextPageConfig();
                        noOrderTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                        noOrderTxt.setVisibility(View.VISIBLE);
                    }
                }

                orderListRecycleAdapter.notifyDataSetChanged();


            } else {
                if (!isLoadMore) {
                    removeNextPageConfig();
                    generateErrorView();
                }

            }

            mIsLoading = false;
        });


    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }


        errorView.setOnRetryListener(() -> getLiveTaskOrderList(false));
    }

    public void removeNextPageConfig() {
        next_page_str = "";
        isNextPageAvailable = false;
        mIsLoading = false;
        orderListRecycleAdapter.removeFooterView();
    }

    public void closeLoader() {
        if (loading_order_list.getVisibility() == View.VISIBLE) {
            loading_order_list.setVisibility(View.GONE);
        }
    }

    public void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LIVE_TASKS"));
        noOrderTxt.setText("Live Tasks");
    }

    @Override
    public void onItemClickList(int position, String pickedFromRes) {
        if (list == null || list.size() == 0) {
            return;
        }


        if (pickedFromRes.equalsIgnoreCase("Call")) {
            boolean isStore = list.get(position).getPickedFromRes().equalsIgnoreCase("No");
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(isStore ? list.get(0).getRestaurantId() : list.get(position).getiTripId())
                    .setPhoneNumber(isStore ? list.get(position).getRestaurantNumber() : list.get(position).getUserNumber())
                    .setToMemberType(isStore ? Utils.CALLTOSTORE : Utils.CALLTOPASSENGER)
                    .setToMemberName(isStore ? list.get(0).getRestaurantName() : list.get(position).getUserName())
                    .setToMemberImage(isStore ? list.get(0).getRestaurantImage() : list.get(position).getPPicName())
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(list.get(position).getiTripId())
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);

        } else if (pickedFromRes.equalsIgnoreCase("Chat")) {
            boolean isStore = list.get(position).getPickedFromRes().equalsIgnoreCase("No");
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(isStore ? list.get(0).getRestaurantId() : list.get(position).getiTripId())
                    .setPhoneNumber(isStore ? list.get(position).getRestaurantNumber() : list.get(position).getUserNumber())
                    .setToMemberType(isStore ? Utils.CALLTOSTORE : Utils.CALLTOPASSENGER)
                    .setToMemberName(isStore ? list.get(0).getRestaurantName() : list.get(position).getUserName())
                    .setToMemberImage(isStore ? list.get(0).getRestaurantImage() : list.get(position).getPPicName())
                    .setBookingNo(isStore ? list.get(0).getOrderNumber() : list.get(position).getOrderNumber())
                    .setOrderId(isStore ? list.get(0).getiOrderId() : list.get(position).getiOrderId())
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(list.get(position).getiTripId())
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.CHAT);
        } else if (pickedFromRes.equalsIgnoreCase("Navigate")) {


            if (list.get(position).isRestaurant().equalsIgnoreCase("Yes")) {
                Bundle bn = new Bundle();
                bn.putString("type", "trackRest");
                bn.putSerializable("TRIP_DATA", data_trip);
                bn.putSerializable("currentTaskData", list.get(position));
                if (!list.get(0).getRestaurantImage().equals("")) {
                    vImage = list.get(0).getRestaurantImage();
                }
                bn.putString("vImage", vImage);
                bn.putString("callid", list.get(0).getRestaurantId());
                bn.putBoolean("isStore", true);
                bn.putString("isGenie", list.get(0).geteBuyAnyService());
                new ActUtils(getActContext()).startActWithData(TrackOrderActivity.class, bn);
            } else {
                Bundle bn = new Bundle();
                bn.putString("type", "trackUser");
                bn.putSerializable("TRIP_DATA", data_trip);
                bn.putSerializable("currentTaskData", list.get(position));
                if (!list.get(position).getPPicName().equals("")) {
                    vImage = list.get(position).getPPicName();
                }
                bn.putString("vImage", vImage);
                bn.putBoolean("isStore", false);
                bn.putString("callid", list.get(position).getPassengerId());
                bn.putString("isGenie", list.get(0).geteBuyAnyService());
                new ActUtils(getActContext()).startActWithData(TrackOrderActivity.class, bn);
            }

        } else {

            Bundle bn = new Bundle();
            data_trip.put("iOrderId", list.get(position).getiOrderId());
            data_trip.put("iTripId", list.get(position).getiTripId());

            data_trip.put("PassengerId", list.get(position).getPassengerId());
            data_trip.put("PName", list.get(position).getUserName());
            data_trip.put("vLastName", list.get(position).getvLastName());
            bn.putSerializable("TRIP_DATA", data_trip);

            bn.putString("isPhotoUploaded", list.get(position).getIsPhotoUploaded());
            if (list.get(position).getPickedFromRes().equalsIgnoreCase("No") || (list.get(position).getPickedFromRes().equalsIgnoreCase("Yes") && list.get(position).getIsPhotoUploaded().equalsIgnoreCase("No") && eBuyAnyService.equalsIgnoreCase("No"))) {
                bn.putString("isDeliver", "No");
                data_trip.put("PPicName", list.get(position).getPPicName());
                bn.putString("PickedFromRes", list.get(position).getPickedFromRes());
                new ActUtils(getActContext()).startActForResult(LiveTrackOrderDetailActivity.class, bn, Utils.ORDER_DETAIL_REQUEST_CODE);
            } else {
                bn.putString("isDeliver", "Yes");
                new ActUtils(getActContext()).startActForResult(LiveTrackOrderDetail2Activity.class, bn, Utils.ORDER_DETAIL_REQUEST_CODE);
            }


        }

    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.ORDER_DETAIL_REQUEST_CODE) {
            getAllLiveTasks();
        }

    }

    public void call(String phoneNumber) {
        try {

            Intent callIntent = new Intent(Intent.ACTION_DIAL);
            callIntent.setData(Uri.parse("tel:" + phoneNumber));
            startActivity(callIntent);

        } catch (Exception e) {
            // TODO: handle exception
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
    }

    public Context getActContext() {
        return LiveTaskListActivity.this;
    }

    @Override
    public void onBackPressed() {

    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(LiveTaskListActivity.this);
        if (i == R.id.backImgView) {
            LiveTaskListActivity.super.onBackPressed();
        }
    }

    @Override
    public void onBottomTabSelected(int position) {
        addBottomBar.setBottomFrg(position);
    }


}

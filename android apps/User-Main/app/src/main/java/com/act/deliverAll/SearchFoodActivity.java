package com.act.deliverAll;

import android.content.Context;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;

import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.deliverAll.FoodSearchAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class SearchFoodActivity extends ParentActivity implements FoodSearchAdapter.OnItemClickList {

    EditText searchTxtView;
    RecyclerView foodSearchRecycView;
    FoodSearchAdapter foodSearchAdapter;
    MTextView cancelTxt;
    ArrayList<HashMap<String, String>> foodSearchArrList = new ArrayList<>();
    ImageView imageCancel;

    ImageView noSearchImage;
    MTextView norecordTxt;
    AVLoadingIndicatorView loaderView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_search_food);

        initView();
    }


    public void initView() {
        searchTxtView = (EditText) findViewById(R.id.searchTxtView);
        imageCancel = (ImageView) findViewById(R.id.imageCancel);
        cancelTxt = (MTextView) findViewById(R.id.cancelTxt);
        noSearchImage = (ImageView) findViewById(R.id.noSearchImage);
        norecordTxt = (MTextView) findViewById(R.id.norecordTxt);
        loaderView = (AVLoadingIndicatorView) findViewById(R.id.loaderView);
        norecordTxt.setVisibility(View.GONE);
        addToClickHandler(cancelTxt);
        addToClickHandler(imageCancel);
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_GENERAL"));

        searchTxtView.setHint(generalFunc.retrieveLangLBl("", "LBL_SEARCH_FOR_ITEMS"));

        // new CreateRoundedView(Color.parseColor("#ECECEC"), 15, 0, getActContext().getResources().getColor(android.R.color.transparent), searchTxtView);

        foodSearchRecycView = findViewById(R.id.foodSearchRecycView);
        foodSearchAdapter = new FoodSearchAdapter(getActContext(), foodSearchArrList);
        foodSearchRecycView.setLayoutManager(new LinearLayoutManager(getActContext()));
        foodSearchRecycView.setAdapter(foodSearchAdapter);
        foodSearchAdapter.setOnItemClickList(this);

        norecordTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEARCH_RESULT"));
        imageCancel.setVisibility(View.GONE);

        searchTxtView.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
                imageCancel.setVisibility(View.GONE);
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                imageCancel.setVisibility(View.GONE);
            }

            @Override
            public void afterTextChanged(Editable s) {

                if (s.length() > 2) {
                    getRestaurantList(searchTxtView.getText().toString().trim());

                } else {
                    if (s.length() == 0) {
                        imageCancel.setVisibility(View.GONE);
                        loaderView.setVisibility(View.GONE);
                    }else {
                        imageCancel.setVisibility(View.VISIBLE);
                    }
                    norecordTxt.setVisibility(View.GONE);
                    loaderView.setVisibility(View.GONE);
                    noSearchImage.setVisibility(View.VISIBLE);
                    foodSearchArrList.clear();
                    foodSearchAdapter.notifyDataSetChanged();
                }

            }
        });


    }

    @Override
    public void onItemClick(int position) {


        Bundle bn = new Bundle();
        foodSearchArrList.get(position).put("Restaurant_Status", restaurantstatus);
        foodSearchArrList.get(position).put("ispriceshow", ispriceshow);
        foodSearchArrList.get(position).put("eAvailable", eAvailable);
        bn.putSerializable("data", foodSearchArrList.get(position));


        new ActUtils(getActContext()).startActWithData(AddBasketActivity.class, bn);


    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == R.id.cancelTxt) {
            onBackPressed();
        } else if (i == R.id.imageCancel) {
            searchTxtView.setText("");
            norecordTxt.setVisibility(View.GONE);
            noSearchImage.setVisibility(View.VISIBLE);
            loaderView.setVisibility(View.GONE);
        }


    }


    String restaurantstatus = "";
    String ispriceshow = "";
    String eAvailable = "";

    public void getRestaurantList(String searchKeyword) {
        loaderView.setVisibility(View.VISIBLE);
        imageCancel.setVisibility(View.GONE);
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "GetRestaurantDetails");
        parameters.put("iCompanyId", getIntent().getStringExtra("iCompanyId"));
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("searchword", searchKeyword);
        parameters.put("CheckNonVegFoodType", getIntent().getStringExtra("CheckNonVegFoodType"));
        parameters.put("eSystem", Utils.eSystem_Type);
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                loaderView.setVisibility(View.GONE);
                loaderView.setVisibility(View.GONE);
                imageCancel.setVisibility(View.VISIBLE);

                if (isDataAvail == true) {
                    foodSearchArrList.clear();
                    String message = generalFunc.getJsonValue("message", responseString);
                    restaurantstatus = generalFunc.getJsonValue("restaurantstatus", responseString);
                    ispriceshow = generalFunc.getJsonValue("ispriceshow", responseString);
                    eAvailable = generalFunc.getJsonValue("eAvailable", responseString);

                    norecordTxt.setVisibility(View.GONE);
                    JSONArray restaurant_Arr = generalFunc.getJsonArray("MenuItemsDetails", message);
                    if (restaurant_Arr != null) {
                        String iCompanyId = "", vCompany = "";
                        if (restaurant_Arr.length() > 0) {
                            iCompanyId = getIntent().getStringExtra("iCompanyId");
                            vCompany = getIntent().getStringExtra("vCompany");
                        }
                        for (int i = 0; i < restaurant_Arr.length(); i++) {
                            JSONObject item_obj = generalFunc.getJsonObject(restaurant_Arr, i);
                            HashMap<String, String> map = new HashMap<>();
                            map.put("iMenuItemId", generalFunc.getJsonValueStr("iMenuItemId", item_obj));
                            map.put("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", item_obj));
                            map.put("vItemType", generalFunc.getJsonValueStr("vItemType", item_obj));
                            map.put("vItemDesc", generalFunc.getJsonValueStr("vItemDesc", item_obj));
                            map.put("fPrice", generalFunc.getJsonValueStr("fPrice", item_obj));
                            map.put("eFoodType", generalFunc.getJsonValueStr("eFoodType", item_obj));

                            String fOfferAmt = generalFunc.getJsonValueStr("fOfferAmt", item_obj);
                            map.put("fOfferAmt", fOfferAmt);
                            map.put("fOfferAmtNotZero", generalFunc.parseDoubleValue(0, fOfferAmt) > 0 ? "Yes" : "No");
                            String vImage = generalFunc.getJsonValueStr("vImage", item_obj);
                            map.put("vImage", Utils.checkText(vImage) ? vImage : "");
                            map.put("iDisplayOrder", generalFunc.getJsonValueStr("iDisplayOrder", item_obj));
                            map.put("StrikeoutPrice", generalFunc.getJsonValueStr("StrikeoutPrice", item_obj));
                            map.put("fDiscountPrice", generalFunc.getJsonValueStr("fDiscountPrice", item_obj));
                            map.put("fDiscountPricewithsymbol", generalFunc.getJsonValueStr("fDiscountPricewithsymbol", item_obj));
                            map.put("MenuItemOptionToppingArr", generalFunc.getJsonValueStr("MenuItemOptionToppingArr", item_obj));
                            map.put("currencySymbol", generalFunc.getJsonValueStr("currencySymbol", item_obj));
                            map.put("iCompanyId", iCompanyId);
                            map.put("vCompany", vCompany);
                            map.put("prescription_required", generalFunc.getJsonValueStr("prescription_required", item_obj));
                            map.put("iMaxItemQty", generalFunc.getJsonValueStr("iMaxItemQty", item_obj));
                            map.put("fMinOrderValue", generalFunc.getJsonValueStr("fMinOrderValue", item_obj));


                            foodSearchArrList.add(map);
                        }
                    }

                    foodSearchAdapter.notifyDataSetChanged();

                    if (foodSearchArrList.size() > 0) {
                        noSearchImage.setVisibility(View.GONE);
                    } else {
                        norecordTxt.setVisibility(View.VISIBLE);
                        noSearchImage.setVisibility(View.GONE);
                    }

                } else {
                    if (foodSearchArrList != null && foodSearchArrList.size() == 0) {
                        norecordTxt.setVisibility(View.VISIBLE);
                        noSearchImage.setVisibility(View.GONE);
                    }
                    foodSearchArrList.clear();
                    foodSearchAdapter.notifyDataSetChanged();

                }
            } else {
                generalFunc.showError();
            }
        });

    }


    public Context getActContext() {
        return SearchFoodActivity.this;
    }
}

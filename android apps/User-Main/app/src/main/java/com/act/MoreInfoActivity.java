package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.act.homescreen23.adapter.HomeUtils;
import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.dialogs.MyCommonDialog;
import com.fragments.GalleryFragment;
import com.fragments.ReviewsFragment;
import com.fragments.ServiceFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.tabs.TabLayout;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemCarouselImageBinding;
import com.realmModel.CarWashCartData;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.CounterFab;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;
import com.view.carouselview.CarouselView;

import java.util.ArrayList;
import java.util.HashMap;

import io.realm.Realm;
import io.realm.RealmResults;

public class MoreInfoActivity extends ParentActivity {

    MTextView titleTxt, bottomViewDescTxt;
    public boolean isVideoConsultEnable;
    ImageView backImgView;

    CharSequence[] titles;
    ImageView rightImgView;
    ArrayList<Fragment> fragmentList = new ArrayList<>();

    ViewPager view_pager;
    RelativeLayout bottomCartView, bottomContinueView;
    MTextView itemNpricecartTxt, viewCartTxt;
    RealmResults<CarWashCartData> realmCartList;
    CounterFab cartView;

    CarouselView carouselView;
    ImageView closeView;
    View carouselContainerView;
    ArrayList<HashMap<String, String>> galleryListData = new ArrayList<>();
    ImageView driverStatus;
    public ImageView searchImgView;
    int SEARCH_CATEGORY = 001;
    private ServiceFragment mServiceFragment;

    private int _11sdp, imgWidth, imgHeight;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_more_info);

        _11sdp = getResources().getDimensionPixelSize(R.dimen._11sdp);
        imgWidth = (int) Utils.getScreenPixelWidth(getActContext()) - (_11sdp * 2);
        imgHeight = (int) Utils.getScreenPixelHeight(getActContext()) - (_11sdp * 2);

        // SimpleRatingBar bottomViewratingBar = (SimpleRatingBar) findViewById(R.id.bottomViewratingBar);
        MTextView nameTxt = (MTextView) findViewById(R.id.bottomViewnameTxt);
        MTextView rateTxt = (MTextView) findViewById(R.id.rateTxt);
        LinearLayout Rating = findViewById(R.id.Rating);

        searchImgView = findViewById(R.id.searchImgView);
        addToClickHandler(searchImgView);
        if (generalFunc.getJsonValueStr("ENABLE_SEARCH_UFX_SERVICES", obj_userProfile) != null &&
                generalFunc.getJsonValueStr("ENABLE_SEARCH_UFX_SERVICES", obj_userProfile).equalsIgnoreCase("Yes")) {
            searchImgView.setVisibility(View.VISIBLE);
        }
        view_pager = (ViewPager) findViewById(R.id.view_pager);

        isVideoConsultEnable = getIntent().getBooleanExtra("isVideoConsultEnable", false);

        bottomViewDescTxt = (MTextView) findViewById(R.id.bottomViewDescTxt);
        if (Utils.checkText(getIntent().getStringExtra("tProfileDescription"))) {
            bottomViewDescTxt.setVisibility(View.VISIBLE);
            bottomViewDescTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_PROFILE_DESCRIPTION"));
            addToClickHandler(bottomViewDescTxt);
        } else {
            bottomViewDescTxt.setVisibility(View.GONE);
        }

        bottomContinueView = (RelativeLayout) findViewById(R.id.bottomContinueView);
        bottomContinueView.setVisibility(View.GONE);
        if (isVideoConsultEnable) {
            bottomViewDescTxt.setVisibility(View.GONE);
            searchImgView.setVisibility(View.GONE);
            addToClickHandler(bottomContinueView);
            MTextView txtContinue = findViewById(R.id.txtContinue);
            txtContinue.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        }

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        bottomCartView = (RelativeLayout) findViewById(R.id.bottomCartView);
        addToClickHandler(bottomCartView);
        cartView = (CounterFab) findViewById(R.id.cartView);
        itemNpricecartTxt = (MTextView) findViewById(R.id.itemNpricecartTxt);
        viewCartTxt = (MTextView) findViewById(R.id.viewCartTxt);
        viewCartTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHECKOUT"));
        backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        rightImgView = (ImageView) findViewById(R.id.rightImgView);
        driverStatus = (ImageView) findViewById(R.id.driverStatus);


        carouselContainerView = findViewById(R.id.carouselContainerView);
        carouselView = (CarouselView) findViewById(R.id.carouselView);
        carouselView.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {
                manageIcon(position);
            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });
        closeView = (ImageView) findViewById(R.id.closeView);
        addToClickHandler(rightImgView);
        TabLayout material_tabs = (TabLayout) findViewById(R.id.material_tabs);
        nameTxt.setText(getIntent().getStringExtra("name"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_DETAIL"));
        int padding = Utils.dipToPixels(getActContext(), 14);
        rightImgView.setPadding(padding, padding, padding, padding);
        rightImgView.setImageResource(R.drawable.ic_information);

        titles = new CharSequence[]{generalFunc.retrieveLangLBl("", "LBL_SERVICES"), generalFunc.retrieveLangLBl("", "LBL_GALLERY"), generalFunc.retrieveLangLBl("", "LBL_REVIEWS")};
        fragmentList.add(generatServiceFrag());
        fragmentList.add(generatGalleryFrag());
        fragmentList.add(generatReviewsFrag());
        addToClickHandler(closeView);

        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        view_pager.setAdapter(adapter);
        material_tabs.setupWithViewPager(view_pager);
        // bottomViewratingBar.setRating(GeneralFunctions.parseFloatValue(0, getIntent().getStringExtra("average_rating")));
        rateTxt.setText(getIntent().getStringExtra("average_rating"));
        if (getIntent().getStringExtra("average_rating").equalsIgnoreCase("")) {
            Rating.setVisibility(View.GONE);
        }

        String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + getIntent().getStringExtra("iDriverId") + "/" + getIntent().getStringExtra("driver_img");

        if (getIntent().getStringExtra("IS_PROVIDER_ONLINE").equalsIgnoreCase("Yes")) {
            driverStatus.setColorFilter(ContextCompat.getColor(getActContext(), R.color.Green));
        } else {
            driverStatus.setColorFilter(ContextCompat.getColor(getActContext(), R.color.Red));
        }


        new LoadImage.builder(LoadImage.bind(image_url), ((SelectableRoundedImageView) findViewById(R.id.bottomViewdriverImgView))).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {
                if (position == 0) {
                    if (generalFunc.getJsonValueStr("ENABLE_SEARCH_UFX_SERVICES", obj_userProfile) != null &&
                            generalFunc.getJsonValueStr("ENABLE_SEARCH_UFX_SERVICES", obj_userProfile).equalsIgnoreCase("Yes")) {
                        if (isVideoConsultEnable) {
                            searchImgView.setVisibility(View.GONE);
                        } else {
                            searchImgView.setVisibility(View.VISIBLE);
                        }
                    }

                } else {

                    searchImgView.setVisibility(View.GONE);
                }

            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });
        view_pager.setOffscreenPageLimit(fragmentList.size());


    }


    public void onResumeCall() {
        onResume();
        if (isVideoConsultEnable) {
            bottomViewDescTxt.setVisibility(View.VISIBLE);
            bottomViewDescTxt.setText(mServiceFragment.eVideoConsultServiceCharge);
            bottomViewDescTxt.setOnClickListener(null);
            bottomContinueView.setVisibility(View.VISIBLE);
            ViewGroup.MarginLayoutParams pagerLayoutParams = (ViewGroup.MarginLayoutParams) view_pager.getLayoutParams();
            pagerLayoutParams.setMargins(0, 0, 0, (int) getResources().getDimension(R.dimen.all_btn_height));
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.UFX_REQUEST_CODE && resultCode == RESULT_OK && data != null) {
            if (data.getBooleanExtra("isVideoConsultEnable", false)) {
                bottomCartView.performClick();
            }
        }
    }

    @Override
    protected void onResume() {
        super.onResume();

        realmCartList = getCartData();
        double finlaTotal = 0;
        String fareType = "";
        ViewGroup.MarginLayoutParams pagerLayoutParams = (ViewGroup.MarginLayoutParams) view_pager.getLayoutParams();
        if (realmCartList.size() > 0) {
            int cnt = 0;
            for (int i = 0; i < realmCartList.size(); i++) {
                if (realmCartList.get(i).isVideoConsultEnable()) {
                    bottomCartView.setVisibility(View.GONE);
                    return;
                }
                CarWashCartData itemPos = realmCartList.get(i);
                cnt = cnt + GeneralFunctions.parseIntegerValue(0, itemPos.getItemCount());
                fareType = itemPos.getCategoryListItem().geteFareType();

                double price = GeneralFunctions.parseDoubleValue(0, itemPos.getFinalTotal().replace(itemPos.getvSymbol(), ""));
                finlaTotal = finlaTotal + price;


            }

            bottomCartView.setVisibility(View.VISIBLE);
            pagerLayoutParams.setMargins(0, 0, 0, (int) getResources().getDimension(R.dimen._55sdp));

            itemNpricecartTxt.setText("");
            if (finlaTotal > 0) {
                if (fareType.equalsIgnoreCase("Hourly") || fareType.equalsIgnoreCase("Fixed")) {
                    itemNpricecartTxt.setText(generalFunc.formatNumAsPerCurrency(generalFunc, "" + generalFunc.convertNumberWithRTL(GeneralFunctions.convertDecimalPlaceDisplay(finlaTotal) + ""), realmCartList.get(0).getvSymbol(), true));
                }
            }


            cartView.setCount(cnt);

        } else {
            bottomCartView.setVisibility(View.GONE);
            pagerLayoutParams.setMargins(0, 0, 0, 0);
        }
    }

    public ServiceFragment generatServiceFrag() {
        mServiceFragment = new ServiceFragment();
        return mServiceFragment;
    }

    public GalleryFragment generatGalleryFrag() {
        GalleryFragment frag = new GalleryFragment();
        Bundle bn = new Bundle();
        frag.setArguments(bn);
        return frag;
    }

    public ReviewsFragment generatReviewsFrag() {
        ReviewsFragment frag = new ReviewsFragment();
        Bundle bn = new Bundle();
        frag.setArguments(bn);
        return frag;
    }

    public RealmResults<CarWashCartData> getCartData() {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(CarWashCartData.class).findAll();
    }

    public void openCarouselView(ArrayList<HashMap<String, String>> galleryListData, int currentPosition) {
        this.galleryListData = galleryListData;
        manageIcon(currentPosition);
        carouselContainerView.setVisibility(View.VISIBLE);
        carouselView.setViewListener(position -> {
            String eFileType = galleryListData.get(position).get("eFileType");
            String vImage = galleryListData.get(position).get("vImage");
            String ThumbImage = galleryListData.get(position).get("ThumbImage");
            LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull ItemCarouselImageBinding iBinding = ItemCarouselImageBinding.inflate(inflater, null, false);

            iBinding.imgViewCarousel.zoomImageView.setPadding(_11sdp, _11sdp, _11sdp, _11sdp);

            if (eFileType.equalsIgnoreCase("Video")) {
                iBinding.playIcon.setVisibility(View.VISIBLE);
                iBinding.playIcon.setOnClickListener(v -> MyCommonDialog.showVideoDialog(MoreInfoActivity.this, ThumbImage, vImage));
                String imageUrl = Utils.getResizeImgURL(getActContext(), ThumbImage, imgWidth, 0, imgHeight);
                HomeUtils.loadImg(getActContext(), iBinding.imgViewCarousel.zoomImageView, imageUrl, R.drawable.ic_novideo__icon, false, 0, 0);
            } else {
                iBinding.playIcon.setVisibility(View.GONE);
                iBinding.playIcon.setOnClickListener(null);
                String imageUrl = Utils.getResizeImgURL(getActContext(), vImage, imgWidth, 0, imgHeight);
                HomeUtils.loadImg(getActContext(), iBinding.imgViewCarousel.zoomImageView, imageUrl, R.mipmap.ic_no_icon, false, 0, 0);
            }

            return iBinding.getRoot();
        });
        carouselView.setPageCount(galleryListData.size());
        carouselView.setCurrentItem(currentPosition);
    }

    private void manageIcon(int pos) {
        manageVectorImage(findViewById(R.id.playIconBtn), R.drawable.ic_play_video, R.drawable.ic_play_video_compat);
        if (galleryListData.get(pos).get("eFileType").equals("Video")) {
            findViewById(R.id.playIconBtn).setVisibility(View.VISIBLE);
        } else {
            findViewById(R.id.playIconBtn).setVisibility(View.GONE);
        }
    }

    @Override
    public void onBackPressed() {
        Realm realm = MyApp.getRealmInstance();
        realm.beginTransaction();
        realm.delete(CarWashCartData.class);
        realm.commitTransaction();
        super.onBackPressed();
    }

    public Context getActContext() {
        return MoreInfoActivity.this;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int id = view.getId();
        if (id == R.id.searchImgView) {
            Bundle bundlesearch = new Bundle();
            bundlesearch.putString("iDriverId", getIntent().getStringExtra("iDriverId"));
            bundlesearch.putString("parentId", getIntent().getStringExtra("parentId"));
            bundlesearch.putString("SelectedVehicleTypeId", getIntent().getStringExtra("SelectedVehicleTypeId"));
            bundlesearch.putString("latitude", getIntent().getStringExtra("latitude"));
            bundlesearch.putString("longitude", getIntent().getStringExtra("longitude"));
            bundlesearch.putString("address", getIntent().getStringExtra("address"));

            new ActUtils(getActContext()).startActForResult(SearchCategoryActivity.class, bundlesearch, SEARCH_CATEGORY);
        } else if (id == R.id.closeView) {
            if (carouselContainerView.getVisibility() == View.VISIBLE) {
                carouselContainerView.setVisibility(View.GONE);
            }
        } else if (id == R.id.backImgView) {
            onBackPressed();
        } else if (id == R.id.bottomContinueView) {
            if (mServiceFragment == null || mServiceFragment.allCategoryItemsList == null || mServiceFragment.allCategoryItemsList.size() == 0) {
                return;
            }
            Bundle bn1 = new Bundle();
            bn1.putAll(getIntent().getExtras());
            bn1.putSerializable("data", mServiceFragment.allCategoryItemsList.get(mServiceFragment.allCategoryItemsList.size() - 1));
            bn1.putString("iDriverId", getIntent().getStringExtra("iDriverId"));
            bn1.putBoolean("isVideoConsultEnable", isVideoConsultEnable);
            new ActUtils(getActContext()).startActForResult(UberxCartActivity.class, bn1, Utils.UFX_REQUEST_CODE);
        } else if (id == R.id.bottomCartView) {
            Bundle bn = new Bundle();
            bn.putString("name", getIntent().getStringExtra("name"));
            bn.putString("serviceName", getIntent().getStringExtra("serviceName"));
            bn.putString("iDriverId", getIntent().getStringExtra("iDriverId"));
            bn.putString("latitude", getIntent().getStringExtra("latitude"));
            bn.putString("longitude", getIntent().getStringExtra("longitude"));
            bn.putString("average_rating", getIntent().getStringExtra("average_rating"));
            bn.putString("driver_img", getIntent().getStringExtra("driver_img"));
            bn.putString("address", getIntent().getStringExtra("address"));
            bn.putString("vProviderLatitude", getIntent().getStringExtra("vProviderLatitude"));
            bn.putString("vProviderLongitude", getIntent().getStringExtra("vProviderLongitude"));
            bn.putBoolean("isVideoConsultEnable", isVideoConsultEnable);
            new ActUtils(getActContext()).startActWithData(CarWashBookingDetailsActivity.class, bn);
        } else if (id == R.id.bottomViewDescTxt) {
            new ActUtils(getActContext()).startActWithData(ProviderInfoActivity.class, getIntent().getExtras());
        }
    }


}

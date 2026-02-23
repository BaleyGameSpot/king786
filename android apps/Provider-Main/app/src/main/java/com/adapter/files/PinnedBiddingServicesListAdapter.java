package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.SectionIndexer;
import android.widget.TextView;

import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.core.content.ContextCompat;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.pinnedListView.CountryListItem;
import com.view.pinnedListView.PinnedSectionListView;

import java.util.ArrayList;

public class PinnedBiddingServicesListAdapter extends BaseAdapter implements PinnedSectionListView.PinnedSectionListAdapter, SectionIndexer {

    private final Context mContext;
    private final GeneralFunctions generalFunctions;
    private CategoryListItem[] sections;
    private LayoutInflater inflater;

    private final ArrayList<CategoryListItem> categoryListItems;
    private final ArrayList<Boolean> biddingTypesStatusArr = new ArrayList<>();

    public PinnedBiddingServicesListAdapter(Context mContext, GeneralFunctions generalFunc, ArrayList<CategoryListItem> categoryListItems, CategoryListItem[] sections) {
        this.mContext = mContext;
        this.generalFunctions = generalFunc;
        this.categoryListItems = categoryListItems;
        this.sections = sections;
    }

    public void manageBiddingArraySize() {
        biddingTypesStatusArr.clear();
        for (int i = 0; i < categoryListItems.size(); i++) {
            biddingTypesStatusArr.add(i, false);
        }
    }

    public void changeSection(CategoryListItem[] sections) {
        this.sections = sections;
    }

    @SuppressLint("InflateParams")
    @Override
    public View getView(int position, View convertView, ViewGroup parent) {

        if (inflater == null)
            inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);

        if (convertView == null)
            convertView = inflater.inflate(R.layout.item_bidding_category_list, null);

        TextView txt_view = convertView.findViewById(R.id.txt);
        LinearLayout serviceArea = convertView.findViewById(R.id.serviceArea);
        LinearLayout itemLayout = convertView.findViewById(R.id.itemLayout);
        RelativeLayout layoutBackground = convertView.findViewById(R.id.layoutBackground);
        ImageView roundImageView = convertView.findViewById(R.id.roundImageView);

        AppCompatCheckBox cbBiddingService = convertView.findViewById(R.id.cbBiddingService);
        cbBiddingService.setTag(position);

        txt_view.setTextColor(Color.BLACK);
        txt_view.setTag("" + position);
        cbBiddingService.setTag("" + position);
        final CategoryListItem categoryListItem = categoryListItems.get(position);

        serviceArea.setClickable(false);
        serviceArea.setEnabled(false);
        if (categoryListItem.getType() == CountryListItem.SECTION) {
            convertView.setBackgroundColor(mContext.getResources().getColor(R.color.white));
            txt_view.setText(categoryListItem.getvTitle());
            txt_view.setText(categoryListItem.getText());
            txt_view.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._14ssp));
            cbBiddingService.setVisibility(View.GONE);
            layoutBackground.setVisibility(View.GONE);
            serviceArea.setMinimumHeight((int) mContext.getResources().getDimension(R.dimen._30sdp));
            txt_view.setMinimumHeight((int) mContext.getResources().getDimension(R.dimen._30sdp));
            txt_view.setGravity(Gravity.BOTTOM | Gravity.START);

        } else {
            itemLayout.setBackground(ContextCompat.getDrawable(mContext, R.drawable.card_view_23_white_shadow));
            LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) itemLayout.getLayoutParams();
            params.setMargins(Utils.dpToPx(15, mContext), Utils.dpToPx(5, mContext), Utils.dpToPx(15, mContext), Utils.dpToPx(5, mContext));
            itemLayout.setPadding(Utils.dpToPx(5, mContext), Utils.dpToPx(5, mContext), Utils.dpToPx(5, mContext), Utils.dpToPx(5, mContext));
            txt_view.setText(categoryListItem.getvTitle());
            txt_view.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._12ssp));
            layoutBackground.setVisibility(View.VISIBLE);
            cbBiddingService.setVisibility(View.VISIBLE);
            if (Utils.checkText(categoryListItem.getvLogo())) {
                new LoadImage.builder(LoadImage.bind(categoryListItem.getvLogo()), roundImageView).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            } else {
                new LoadImage.builder(LoadImage.bind(R.mipmap.ic_no_icon), roundImageView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            }
            serviceArea.setMinimumHeight((int) mContext.getResources().getDimension(R.dimen._40sdp));
        }

        // Set data
        // biddingTypesStatusArr.add(position, false);
        String eServiceStatus = categoryListItem.getvCategory();

        cbBiddingService.setButtonDrawable(R.drawable.checkbox_selector);

        if (eServiceStatus.equalsIgnoreCase("Active")) {
            cbBiddingService.setChecked(true);
            biddingTypesStatusArr.set(position, true);
        } else if (eServiceStatus.equalsIgnoreCase("Pending")) {
            cbBiddingService.setButtonDrawable(R.drawable.ic_mark_gray);
            cbBiddingService.setChecked(true);
            biddingTypesStatusArr.set(position, true);
        } else if (eServiceStatus.equalsIgnoreCase("Inactive")) {
            cbBiddingService.setChecked(false);
            biddingTypesStatusArr.set(position, false);
        } else if (eServiceStatus.equalsIgnoreCase("select")) {
            cbBiddingService.setChecked(true);
            biddingTypesStatusArr.set(position, true);
        } else if (eServiceStatus.equalsIgnoreCase("deselect")) {
            cbBiddingService.setChecked(false);
            biddingTypesStatusArr.set(position, false);
        }


        final int finalI = position;
        cbBiddingService.setOnClickListener(v -> {
            if (eServiceStatus.equalsIgnoreCase("Pending")) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        generateAlert.closeAlertBox();
                    }
                });
                generateAlert.setContentMessage("", generalFunctions.retrieveLangLBl("", "LBL_BIDDING_SERVICE_REQUEST_PENDING"));
                generateAlert.setPositiveBtn(generalFunctions.retrieveLangLBl("", "LBL_OK"));
                generateAlert.showAlertBox();
            } else if (eServiceStatus.equalsIgnoreCase("Active")) {
                if (!cbBiddingService.isChecked()) {
                    GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        if (btn_id == 0) {
                            biddingTypesStatusArr.set(finalI, true);
                            cbBiddingService.setChecked(true);
                        } else {
                            biddingTypesStatusArr.set(finalI, false);
                        }
                    });
                    generateAlert.setContentMessage("", generalFunctions.retrieveLangLBl("", "LBL_UNSELECT_CHECKBOX_FOR_BIDDING_SERVICE"));
                    generateAlert.setPositiveBtn(generalFunctions.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.setNegativeBtn(generalFunctions.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                    generateAlert.showAlertBox();
                }
            } else {
                categoryListItem.setvCategory(cbBiddingService.isChecked() ? "select" : "deselect");
                biddingTypesStatusArr.set(finalI, cbBiddingService.isChecked());
            }
        });
        return convertView;
    }

    public String getSelectedIDList() {
        String carTypes = "";
        for (int j = 0; j < biddingTypesStatusArr.size(); j++) {
            try {
                if (biddingTypesStatusArr.get(j) != null && biddingTypesStatusArr.get(j)) {
                    String iVehicleTypeId = categoryListItems.get(j).getiVehicleCategoryId();
                    carTypes = carTypes.equals("") ? iVehicleTypeId : (carTypes + "," + iVehicleTypeId);
                }
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
        }
        return carTypes;
    }

    @Override
    public int getViewTypeCount() {
        return 2;
    }

    @Override
    public CategoryListItem[] getSections() {
        return sections;
    }

    @Override
    public int getPositionForSection(int section) {
        if (section >= sections.length) {
            section = sections.length - 1;
        }
        return sections[section].getListPosition();
    }

    @Override
    public int getSectionForPosition(int position) {
        if (position >= getCount()) {
            position = getCount() - 1;
        }
        return categoryListItems.get(position).getSectionPosition();
    }

    @Override
    public int getItemViewType(int position) {
        return categoryListItems.get(position).getType();
    }

    @Override
    public boolean isItemViewTypePinned(int viewType) {
        return viewType == CountryListItem.SECTION;
    }

    @Override
    public int getCount() {
        return categoryListItems.size();
    }

    @Override
    public Object getItem(int position) {
        return categoryListItems.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }
}
package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.SectionIndexer;
import android.widget.TextView;

import androidx.core.content.ContextCompat;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;
import com.view.pinnedListView.CountryListItem;
import com.view.pinnedListView.PinnedSectionListView;

import java.util.ArrayList;

public class PinnedCategorySectionListAdapter extends BaseAdapter implements PinnedSectionListView.PinnedSectionListAdapter, SectionIndexer {

    private final Context mContext;
    private final GeneralFunctions generalFunctions;
    private CategoryListItem[] sections;
    private LayoutInflater inflater;
    ArrayList<CategoryListItem> categoryListItems;
    ServiceClick serviceClickList;
    boolean isStateList = false;
    private final String LBL_HOUR, LBL_MINIMUM_TXT, LBL_HOURS_TXT, LBL_REMOVE_SERVICE, LBL_BOOK_SERVICE;

    public PinnedCategorySectionListAdapter(Context mContext, GeneralFunctions generalFunc, ArrayList<CategoryListItem> categoryListItems, CategoryListItem[] sections) {
        this.mContext = mContext;
        this.categoryListItems = categoryListItems;
        this.sections = sections;
        generalFunctions = generalFunc;
        LBL_HOUR = generalFunctions.retrieveLangLBl("hour", "LBL_HOUR");
        LBL_HOURS_TXT = generalFunctions.retrieveLangLBl("", "LBL_HOURS_TXT");
        LBL_MINIMUM_TXT = generalFunctions.retrieveLangLBl("", "LBL_MINIMUM_TXT");
        LBL_REMOVE_SERVICE = generalFunctions.retrieveLangLBl("", "LBL_REMOVE_SERVICE");
        LBL_BOOK_SERVICE = generalFunctions.retrieveLangLBl("", "LBL_BOOK_SERVICE");
    }

    public void changeSection(CategoryListItem[] sections) {
        this.sections = sections;
    }

    public void isStateList(boolean value) {
        this.isStateList = value;
    }

    @SuppressLint({"SetTextI18n", "InflateParams"})
    @Override
    public View getView(int position, View convertView, ViewGroup parent) {

        if (inflater == null)
            inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);

        if (convertView == null)
            convertView = inflater.inflate(R.layout.service_list_item, null);

        TextView headingTxt = convertView.findViewById(R.id.headingTxt);
        TextView headingTitleTxt = convertView.findViewById(R.id.headingTitleTxt);
        TextView descTxt = convertView.findViewById(R.id.descTxt);
        TextView amountTxt = convertView.findViewById(R.id.amountTxt);
        TextView bookRemoveTxt = convertView.findViewById(R.id.bookRemoveTxt);
        MTextView btnTxt = convertView.findViewById(R.id.btnTxt);
        ImageView btnImg = convertView.findViewById(R.id.btnImg);
        LinearLayout btnArea = convertView.findViewById(R.id.btnArea);
        LinearLayout detalisArea = convertView.findViewById(R.id.detalisArea);


        headingTxt.setTextColor(Color.BLACK);
        headingTxt.setTag("" + position);
        final CategoryListItem categoryListItem = categoryListItems.get(position);

        if (categoryListItem.type == CountryListItem.SECTION) {
            detalisArea.setClickable(false);
            detalisArea.setEnabled(false);
            headingTitleTxt.setText(categoryListItem.getvTitle());
            headingTitleTxt.setVisibility(View.VISIBLE);
            headingTitleTxt.setText(categoryListItem.text);
            detalisArea.setVisibility(View.GONE);
        } else {
            if (categoryListItem.getvShortDesc().equalsIgnoreCase("") || categoryListItem.getvDesc().equalsIgnoreCase("")) {
                descTxt.setVisibility(View.GONE);
            } else {
                descTxt.setVisibility(View.VISIBLE);
            }
            String vShortDesc = categoryListItem.getvShortDesc();

            //descTxt.setText(GeneralFunctions.fromHtml(Utils.checkText(vShortDesc) ? vShortDesc : categoryListItem.getvDesc()));
            descTxt.setText(vShortDesc);
            headingTxt.setText(categoryListItem.getvTitle());
            headingTitleTxt.setVisibility(View.GONE);

            if (categoryListItem.isAdd) {
                btnTxt.setText(LBL_REMOVE_SERVICE);
                btnArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.remove_border));
                if (generalFunctions.isRTLmode()) {
                    btnImg.setRotation(180);
                    btnArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.remove_border_rtl));
                }
            } else {
                btnTxt.setText(LBL_BOOK_SERVICE);
                btnArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.login_border));
                if (generalFunctions.isRTLmode()) {
                    btnImg.setRotation(180);
                    btnArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.login_border_rtl));
                }
            }

            String FareType = categoryListItem.geteFareType();

            if (FareType.equalsIgnoreCase("Fixed")) {
                amountTxt.setText(generalFunctions.convertNumberWithRTL(categoryListItem.getfFixedFare()));
            } else if (FareType.equalsIgnoreCase("Hourly")) {
                String hourlyPrice = generalFunctions.convertNumberWithRTL(categoryListItem.getfPricePerHour());
                amountTxt.setText(hourlyPrice + "/" + LBL_HOUR);
                if (GeneralFunctions.parseDoubleValue(0, categoryListItem.getfMinHour()) > 1) {
                    amountTxt.setText(hourlyPrice + "/" + LBL_HOUR + "\n" + "(" + LBL_MINIMUM_TXT + " " + categoryListItem.getfMinHour() + " " + LBL_HOURS_TXT + ")");
                }
            } else {
                amountTxt.setText("");
            }

            detalisArea.setClickable(true);
            detalisArea.setEnabled(true);

            if (categoryListItem.isVideoConsultEnable()) {
                amountTxt.setVisibility(View.INVISIBLE);
                btnArea.setVisibility(View.GONE);
                headingTxt.setVisibility(View.GONE);
                if (Utils.checkText(categoryListItem.getvDesc())) {
                    descTxt.setMaxLines(Integer.MAX_VALUE);
                    descTxt.setVisibility(View.VISIBLE);
                    descTxt.setText(categoryListItem.getvDesc());
                }
                detalisArea.setClickable(false);
                detalisArea.setEnabled(false);
            }
        }

        detalisArea.setOnClickListener(v -> {
            if (serviceClickList != null) {
                serviceClickList.serviceClickList(categoryListItem);
            }
        });

        btnArea.setOnClickListener(view -> {
            if (categoryListItem.isAdd) {
                if (serviceClickList != null) {
                    serviceClickList.serviceRemoveClickList(categoryListItem);
                }
            } else {
                if (serviceClickList != null) {
                    serviceClickList.serviceClickList(categoryListItem);
                }
            }
        });

        return convertView;
    }

    public interface ServiceClick {
        void serviceClickList(CategoryListItem serviceListItem);

        void serviceRemoveClickList(CategoryListItem serviceListItem);
    }

    public void setserviceClickListener(ServiceClick serviceClickList) {
        this.serviceClickList = serviceClickList;
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
        return sections[section].listPosition;
    }

    @Override
    public int getSectionForPosition(int position) {
        if (position >= getCount()) {
            position = getCount() - 1;
        }
        return categoryListItems.get(position).sectionPosition;
    }

    @Override
    public int getItemViewType(int position) {
        try {
            return categoryListItems.get(position).type;
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
        return 0;
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
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

import androidx.core.content.ContextCompat;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.pinnedListView.CountryListItem;
import com.view.pinnedListView.PinnedSectionListView;

import java.util.ArrayList;

public class PinnedCategorySectionListAdapter extends BaseAdapter implements PinnedSectionListView.PinnedSectionListAdapter, SectionIndexer {

    private final Context mContext;
    private final GeneralFunctions generalFunctions;
    private CountryClick countryClickList;

    private CategoryListItem[] sections;
    private LayoutInflater inflater;

    private final ArrayList<CategoryListItem> categoryListItems;


    public PinnedCategorySectionListAdapter(Context mContext, GeneralFunctions generalFunc, ArrayList<CategoryListItem> categoryListItems, CategoryListItem[] sections) {
        this.mContext = mContext;
        this.generalFunctions = generalFunc;
        this.categoryListItems = categoryListItems;
        this.sections = sections;
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
            convertView = inflater.inflate(R.layout.category_section_list_item, null);

        TextView txt_view = convertView.findViewById(R.id.txt);
        LinearLayout serviceArea = convertView.findViewById(R.id.serviceArea);
        LinearLayout itemLayout = convertView.findViewById(R.id.itemLayout);
        RelativeLayout layoutBackground = convertView.findViewById(R.id.layoutBackground);

        ImageView imgVideoConsult = convertView.findViewById(R.id.imgVideoConsult);
        imgVideoConsult.setVisibility(View.GONE);

        ImageView rightImage = convertView.findViewById(R.id.rightImage);
        rightImage.setVisibility(View.VISIBLE);
        ImageView roundImageView = convertView.findViewById(R.id.roundImageView);

        if (generalFunctions != null && generalFunctions.isRTLmode()) {
            rightImage.setRotationY(180);
            imgVideoConsult.setRotationY(180);
        }

        txt_view.setTextColor(Color.BLACK);
        txt_view.setTag("" + position);
        final CategoryListItem categoryListItem = categoryListItems.get(position);

        if (categoryListItem.getType() == CountryListItem.SECTION) {
            convertView.setBackgroundColor(mContext.getResources().getColor(R.color.white));
            serviceArea.setClickable(false);
            serviceArea.setEnabled(false);
            txt_view.setText(categoryListItem.getvTitle());
            txt_view.setText(categoryListItem.getText());
            txt_view.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._14ssp));
            rightImage.setVisibility(View.GONE);
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
            serviceArea.setClickable(true);
            txt_view.setTextSize(TypedValue.COMPLEX_UNIT_PX, mContext.getResources().getDimension(R.dimen._12ssp));
            serviceArea.setEnabled(true);
            layoutBackground.setVisibility(View.VISIBLE);

            if (Utils.checkText(categoryListItem.getvLogo())) {
                new LoadImage.builder(LoadImage.bind(categoryListItem.getvLogo()), roundImageView).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            } else {
                new LoadImage.builder(LoadImage.bind(R.mipmap.ic_no_icon), roundImageView).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            }
            serviceArea.setMinimumHeight((int) mContext.getResources().getDimension(R.dimen._40sdp));

            if (categoryListItem.getVideoConsultEnable().equalsIgnoreCase("Yes")) {
                imgVideoConsult.setVisibility(View.VISIBLE);
                if (categoryListItem.getVideoConsultEnableProvider().equalsIgnoreCase("Yes")) {
                    imgVideoConsult.setColorFilter(mContext.getResources().getColor(R.color.appThemeColor_1));
                } else {
                    imgVideoConsult.setColorFilter(mContext.getResources().getColor(R.color.gray));
                }
            }
        }

        serviceArea.setOnClickListener(v -> {
            if (countryClickList != null) {
                countryClickList.countryClickList(categoryListItem);
            }
        });

        return convertView;
    }

    public interface CountryClick {
        void countryClickList(CategoryListItem countryListItem);
    }

    public void setCountryClickListener(CountryClick countryClickList) {
        this.countryClickList = countryClickList;
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
package com.adapter.files;

import android.content.Context;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.SectionIndexer;
import android.widget.TextView;

import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.view.pinnedListView.CountryListItem;
import com.view.pinnedListView.PinnedSectionListView;

import java.util.ArrayList;

public class OrganizationPinnedSectionListAdapter extends BaseAdapter implements PinnedSectionListView.PinnedSectionListAdapter, SectionIndexer {

    Context mContext;
    ArrayList<OrganizationListItem> organizationListItems;
    OrganizationClick organizationClick;
    private LayoutInflater inflater;
    GeneralFunctions generalFunctions;
    int lastCheckedItem = -1;


    public OrganizationPinnedSectionListAdapter(Context mContext, ArrayList<OrganizationListItem> organizationListItems) {
        // TODO Auto-generated constructor stub
        this.mContext = mContext;
        this.organizationListItems = organizationListItems;
        generalFunctions = MyApp.getInstance().getGeneralFun(mContext);

    }


    @Override
    public View getView(int position, View convertView, ViewGroup parent) {

        if (inflater == null)
            inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);

        if (convertView == null)
            convertView = inflater.inflate(R.layout.organization_list_item, null);


        TextView txt_view = (TextView) convertView.findViewById(R.id.txt);
        ImageView checkbox = (ImageView) convertView.findViewById(R.id.checkbox);
        txt_view.setTextColor(Color.BLACK);
        txt_view.setTag("" + position);
        View seperator = (View) convertView.findViewById(R.id.seperator);
        LinearLayout linearLayout = (LinearLayout) convertView.findViewById(R.id.ll_item);
        final OrganizationListItem organizeListItem = organizationListItems.get(position);
        txt_view.setText(organizeListItem.text);
        linearLayout.setClickable(true);
        linearLayout.setEnabled(true);
        organizeListItem.setSelected(position == lastCheckedItem);
        if (organizeListItem.getIsSelected()) {
            linearLayout.setBackgroundColor(mContext.getResources().getColor(R.color.appThemeColor_1));
            txt_view.setTextColor(mContext.getResources().getColor(R.color.white));
            checkbox.setImageDrawable(mContext.getResources().getDrawable(R.drawable.selected_tick));
        } else {
            linearLayout.setBackgroundColor(mContext.getResources().getColor(R.color.white));
            txt_view.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));
            checkbox.setImageDrawable(mContext.getResources().getDrawable(R.drawable.not_selected));
        }
        linearLayout.setOnClickListener(new OnClickListener() {

            @Override
            public void onClick(View v) {
                lastCheckedItem = position;
                linearLayout.setClipToOutline(true);
                notifyDataSetChanged();
                if (organizationClick != null) {
                    organizationClick.OrganizationClickList(organizeListItem);
                }
            }
        });

        return convertView;
    }


    @Override
    public int getViewTypeCount() {
        return 2;
    }

    @Override
    public OrganizationListItem[] getSections() {
        return null;
    }

    @Override
    public int getPositionForSection(int section) {

        return 0;
    }

    @Override
    public int getSectionForPosition(int position) {
        if (position >= getCount()) {
            position = getCount() - 1;
        }
        return organizationListItems.get(position).sectionPosition;
    }

    @Override
    public int getItemViewType(int position) {
        return organizationListItems.get(position).type;
    }

    @Override
    public boolean isItemViewTypePinned(int viewType) {
        return viewType == CountryListItem.SECTION;
    }

    @Override
    public int getCount() {

        return organizationListItems.size();
    }

    @Override
    public Object getItem(int position) {

        return organizationListItems.get(position);
    }

    @Override
    public long getItemId(int position) {

        return position;
    }

    public void setOrganizationClickListener(OrganizationClick organizationClick) {
        this.organizationClick = organizationClick;

    }


    public interface OrganizationClick {
        void OrganizationClickList(OrganizationListItem organizationListItem);
    }

}

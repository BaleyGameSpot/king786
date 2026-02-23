package com.act;

import android.os.Bundle;

import androidx.annotation.IdRes;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;

import com.activity.ParentActivity;
import com.fragments.MyProfileFragment;

public class MenuSettingActivity extends ParentActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        //setContentView(R.layout.activity_menu_setting);


        MyProfileFragment frag = new MyProfileFragment();
        Bundle bn = new Bundle();
        bn.putBoolean("isback", true);
        if (getIntent().hasExtra("isProfile")) {
            bn.putBoolean("isProfile", true);
        }
        frag.setArguments(bn);
        addFragment(android.R.id.content,
                frag, "MyProfileFrag");

    }

    protected void addFragment(@IdRes int containerViewId,
                               @NonNull Fragment fragment,
                               @NonNull String fragmentTag) {


        getSupportFragmentManager()
                .beginTransaction()
                .add(containerViewId, fragment, fragmentTag)
                .disallowAddToBackStack()
                .commit();
    }
}

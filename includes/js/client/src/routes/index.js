import React from 'react';
import { Route, IndexRoute } from 'react-router';

// NOTE: here we're making use of the `resolve.root` configuration
// option in webpack, which allows us to specify import paths as if
// they were from the root of the ~/src directory. This makes it
// very easy to navigate to files regardless of how deeply nested
// your current file is.
import CoreLayout from 'layouts/CoreLayout/CoreLayout';
import SiteState from 'views/SiteState';
import WidgetList from 'views/WidgetList/WidgetList';
import WidgetPage from 'views/WidgetList/WidgetPage';

export default (store) => (
  <Route path='/' component={CoreLayout}>
    <IndexRoute component={SiteState} />
    <Route path="/dashboard" component={WidgetList}/>
    <Route path="/dashboard/:widget" component={WidgetPage}/>
    <Route path="/dashboard/:widget/:individual" component={WidgetPage}/>
    <Route path="/dashboard/:widget/:individual/edit" component={WidgetPage}/>
  </Route>
);
